<?php

namespace App\Observers;

use App\Models\AutoselectResult;

class AutoselectResultObserver
{
    /**
     *
     * @param \App\Models\AutoselectResult $autoselectResult
     *
     * @return void
     */
    public function creating(AutoselectResult $autoselectResult)
    {
        $this->setCrtcValues($autoselectResult);
    }

    /**
     *
     * @param \App\Models\AutoselectResult $autoselectResult
     *
     * @return void
     */
    public function updating(AutoselectResult $autoselectResult)
    {
        $this->setCrtcValues($autoselectResult);
    }

    private function setCrtcValues($autoselectResult)
    {
        $autoselectResult->crtc = $autoselectResult->popularity != 0 ?
            $autoselectResult->cart_add_count / $autoselectResult->popularity
            : 0;
        $autoselectResult->category_crtc = $autoselectResult->category_popularity != 0 ?
            $autoselectResult->category_cart_add_count / $autoselectResult->category_popularity
            : 0;
    }
}
