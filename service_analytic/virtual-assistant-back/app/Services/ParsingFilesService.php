<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;

/**
 * Class ParsingFilesService
 *
 * @package App\Services
 */
class ParsingFilesService
{
    const DIRECTION_IN = 'input';
    const DIRECTION_OUT = 'output';

    /** @var string $direction Направление работы */
    protected $direction;
    /** @var MessageBag $errors Ошибки */
    protected $errors;

    /**
     * ParsingFilesService constructor.
     *
     * @param string $direction
     */
    public function __construct($direction = self::DIRECTION_IN)
    {
        $this->direction = $direction;
        $this->errors = new MessageBag();
    }

    /**
     * Направление соединения с FTP
     *
     * @param string $direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    /**
     * Скачать файл с FTP в локальную папку
     *
     * @param $filePath
     *
     * @return bool|string
     */
    public function loadFromFtp($filePath)
    {
        // Путь, куда будем сохранять файл
        $localPath = base_path(config('sources.parser_local_path') . $filePath);

        // Проверяем, что нужно подрубиться по FTP
        if ($this->checkFtp()) {
            // Открываем соединение
            $ftpConnect = $this->connectFtp();
            if (!$ftpConnect) {
                return false;
            }

            // Проверяем, что файл существует
            if (ftp_size($ftpConnect, $filePath) == -1) {
                $this->errors->add('file_not_found', __('parsing.parsing_ftp_file_not_found', [
                    'datetime' => Carbon::now()->toDateTimeString(),
                    'file' => $filePath
                ]));
                ftp_close($ftpConnect);
                return false;
            }

            // Скачиваем файл
            if (!ftp_get($ftpConnect, $localPath, $filePath, FTP_BINARY)) {
                $this->errors->add('loading_file_error', __('parsing.parsing_ftp_file_loading_error', [
                    'file' => $filePath
                ]));
                ftp_close($ftpConnect);
                return false;
            }

            // Закрываем соединение
            ftp_close($ftpConnect);
        }

        return $localPath;
    }

    /**
     * Скачать файл из локальной папки в FTP
     *
     * @param $filePath
     *
     * @return bool
     */
    public function loadToFtp($filePath)
    {
        // Локальный файл
        $localPath = base_path(config('sources.parser_local_path') . $filePath);

        // Проверяем, что файл существует
        if (!file_exists($localPath)) {
            $this->errors->add('export_file_error', __('parsing.local_file_not_found', [
                'file' => $localPath
            ]));
            return false;
        }

        // Проверяем, что нужно подрубиться по FTP
        if ($this->checkFtp()) {
            // Открываем соединение
            $ftpConnect = $this->connectFtp();
            if (!$ftpConnect) {
                return false;
            }

            // Отправлем файл
            if (!ftp_put($ftpConnect, $filePath, $localPath)) {
                $this->errors->add('export_file_error', __('parsing.parsing_ftp_file_saving_error', [
                    'file' => $filePath
                ]));
                return false;
            }

            // Закрываем соединение
            ftp_close($ftpConnect);
        }

        return true;
    }

    /**
     * Проверка, нужно ли работать по FTP
     *
     * @return bool
     */
    protected function checkFtp()
    {
        $ftpHost = config('sources.' . $this->direction . '_parsing_ftp_host');

        return $ftpHost && strstr($ftpHost, URL::to('/')) === false;
    }

    /**
     * Открыть FTP соединение
     *
     * @return bool|resource
     */
    protected function connectFtp()
    {
        $ftpHost = config('sources.' . $this->direction . '_parsing_ftp_host');
        $ftpUser = config('sources.' . $this->direction . '_parsing_ftp_user');
        $ftpPass = config('sources.' . $this->direction . '_parsing_ftp_pass');

        // установить соединение или выйти
        $ftpConnect = ftp_connect($ftpHost) or die(__('parsing.parsing_ftp_not_found', ['host' => $ftpHost])) . "\r\n";

        // попытка входа
        if (!ftp_login($ftpConnect, $ftpUser, $ftpPass)) {
            $this->errors->add('ftp_auth_error', __('parsing.parsing_ftp_auth_error', [
                'host' => $ftpHost,
                'user' => $ftpUser
            ]));
            return false;
        }

        if (!ftp_pasv($ftpConnect, true)) {
            $this->errors->add('ftp_auth_error', __('parsing.parsing_ftp_switch_to_passive_mode_error', [
                'host' => $ftpHost,
            ]));
            return false;
        }

        return $ftpConnect;
    }

    /**
     * Вернуть последнюю ошибку
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors->getMessages();
    }
}
