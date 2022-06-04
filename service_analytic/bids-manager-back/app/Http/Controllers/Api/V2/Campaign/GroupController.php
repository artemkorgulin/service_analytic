<?php

namespace App\Http\Controllers\Api\V2\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Models\Group;
use App\Http\Requests\V2\Campaign\GroupStoreRequest;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GroupController extends Controller
{
    /**
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function index(Campaign $campaign)
    {
        $groupList = $campaign->groups()->notDeleted()->with(['products'])->get();

        return response()->api(true, ['groupList' => $groupList], []);
    }

    /**
     * @param  GroupStoreRequest  $request
     * @param  Campaign  $campaign
     * @return JsonResponse
     */
    public function store(GroupStoreRequest $request, Campaign $campaign)
    {
        $group = new Group();
        $group = $group->saveGroup($request->all(), $campaign);

        return response()->api(true, ['group_id' => $group->id], []);
    }

    /**
     * @param  Campaign  $campaign
     * @param  Group  $group
     * @return JsonResponse
     */
    public function show(Campaign $campaign, Group $group)
    {
        $group = $group->with(['products'])->where('id', '=', $group->id)->first();

        return response()->api(true, $group, []);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  GroupStoreRequest  $request
     * @param  Campaign  $campaign
     * @param  Group  $group
     * @return Response
     */
    public function update(GroupStoreRequest $request, Campaign $campaign, Group $group)
    {
        $group = $group->saveGroup($request->all(), $campaign);

        return response()->api(true, ['group_id' => $group->id], []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Campaign  $campaign
     * @param  Group  $group
     * @return Response
     */
    public function destroy(Campaign $campaign, Group $group)
    {
        // TODO удалить когда будет изменена связка данных
        CampaignProduct::where([['group_id', '=', $group->id], ['campaign_id', '=', $campaign->id]])->update([
            'group_id' => null,
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);
        $group->status_id = Status::DELETED;
        $group->save();

        return response()->api(true, ['group_id' => $group->id], []);
    }
}
