<?php

use App\Models\Strategy;
use App\Models\StrategyHistory;
use App\Models\StrategyShows;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertStrategiesData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->convertStrategyShows();
        $this->convertStrategyShowsStatistics();
        $this->convertStrategyHistories();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    /**
     * Конвертация стратегий
     */
    protected function convertStrategyShows()
    {
        $strategies = Strategy::all();

        foreach( $strategies as $strategy ) {
            StrategyShows::firstOrCreate(
                [
                    'strategy_id' => $strategy->id,
                ],
                [
                    'strategy_id' => $strategy->id,
                    'threshold' => $strategy->threshold,
                    'step' => $strategy->step,
                ]
            );
        }
    }

    /**
     * Конвертация статистики
     */
    protected function convertStrategyShowsStatistics()
    {
        DB::table('strategy_shows_keyword_statistics')
            ->join('strategies_shows', 'strategy_shows_keyword_statistics.strategy_id', '=', 'strategies_shows.strategy_id')
            ->update(['strategy_shows_id' => DB::raw('strategies_shows.id')]);
    }

    /**
     * Конвертация истории изменений
     */
    protected function convertStrategyHistories()
    {
        $strategiesChanges = DB::table('strategy_changes')->get();

        $prevRecs = [];
        foreach( $strategiesChanges as $strategyChange )
        {
            if( array_key_exists($strategyChange->strategy_id, $prevRecs) ) {

                if( !is_null($strategyChange->threshold) ) {
                    StrategyHistory::insert(
                        [
                            'strategy_id'  => $strategyChange->strategy_id,
                            'created_at'   => $strategyChange->created_at,
                            'updated_at'   => $strategyChange->updated_at,
                            'parameter'    => 'threshold',
                            'value_before' => $prevRecs[$strategyChange->strategy_id]->threshold,
                            'value_after'  => $strategyChange->threshold,
                        ]
                    );
                    $prevRecs[$strategyChange->strategy_id]->threshold = $strategyChange->threshold;
                }

                if( !is_null($strategyChange->step) ) {
                    StrategyHistory::insert(
                        [
                            'strategy_id'  => $strategyChange->strategy_id,
                            'created_at'   => $strategyChange->created_at,
                            'updated_at'   => $strategyChange->updated_at,
                            'parameter'    => 'step',
                            'value_before' => $prevRecs[$strategyChange->strategy_id]->step,
                            'value_after'  => $strategyChange->step,
                        ]
                    );
                    $prevRecs[$strategyChange->strategy_id]->step = $strategyChange->step;
                }

                if( !is_null($strategyChange->status_id) ) {
                    StrategyHistory::insert(
                        [
                            'strategy_id'  => $strategyChange->strategy_id,
                            'created_at'   => $strategyChange->created_at,
                            'updated_at'   => $strategyChange->updated_at,
                            'parameter'    => 'status_id',
                            'value_before' => $prevRecs[$strategyChange->strategy_id]->status_id,
                            'value_after'  => $strategyChange->status_id,
                        ]
                    );
                    $prevRecs[$strategyChange->strategy_id]->status_id = $strategyChange->status_id;
                }

            } else {
                $prevRecs[$strategyChange->strategy_id] = $strategyChange;
            }
        }
    }
}
