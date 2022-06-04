<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

class User extends Model
{

    use HasFactory;

    protected $connection = 'wab';

    protected $fillable = ['telegram_user_id'];


    /**
     * Get user by telegram user id
     *
     * @param  numeric  $telegramUserId
     *
     * @return User|null
     */
    public static function findByTelegramId(string|int $telegramUserId): ?User
    {
        return self::where('telegram_user_id', $telegramUserId)->first();
    }


    /**
     * Get user schemas for specified way code
     *
     * @param  string  $wayCode
     * @param  string[]  $fields
     *
     * @return mixed
     */
    public function getSchemas(string $wayCode, ?array $fields = ['id', 'type_id'])
    {
        return NotificationSchema::select($fields)
            ->ofUser($this)
            ->ofWayCode($wayCode)
            ->get()->keyBy('type_id');
    }


    /**
     * Get verification token or generate new one
     *
     * @param  bool  $updateModel  updates model in db if set to true
     *
     * @return string
     */
    public function getOrGenerateVerificationToken(bool $updateModel = true): string
    {
        if ($this->verification_token) {
            return $this->verification_token;
        }

        $verificationToken = Str::random(60);

        if ($updateModel && $this->id) {
            $this->where('id', $this->id)->update(['verification_token' => $verificationToken]);
            $this->verification_token = $verificationToken;
        }

        return $verificationToken;
    }
}
