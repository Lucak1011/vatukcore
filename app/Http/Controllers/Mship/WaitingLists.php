<?php

namespace App\Http\Controllers\Mship;

use App\Http\Controllers\BaseController;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Services\Training\WaitingListSelfEnrolment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WaitingLists extends BaseController
{
    public function index(Request $request)
    {
        /** @var Collection<WaitingListAccount> $waitingListAccounts */
        $waitingListAccounts = $request->user()->waitingListAccounts;

        $atcWaitingListAccounts = collect();
        $pilotWaitingListAccounts = collect();

        foreach ($waitingListAccounts as $waitingListAccount) {
            if ($waitingListAccount->waitingList->department == WaitingList::ATC_DEPARTMENT) {
                $atcWaitingListAccounts->push($waitingListAccount);
            }

            if ($waitingListAccount->waitingList->department == WaitingList::PILOT_DEPARTMENT) {
                $pilotWaitingListAccounts->push($waitingListAccount);
            }
        }

        return view('mship.waiting-lists.index', [
            'atcWaitingListAccounts' => $atcWaitingListAccounts,
            'atcSelfEnrolmentLists' => WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($request->user()),
            'pilotWaitingListAccounts' => $pilotWaitingListAccounts,
        ]);
    }

    public function selfEnrol(WaitingList $waitingList, Request $request)
    {
        $this->authorize('selfEnrol', $waitingList);

        $waitingList->addToWaitingList($request->user(), $request->user());

        return redirect()
            ->route('mship.waiting-lists.index')
            ->with('success', 'You have been added to the waiting list.');
    }
}
