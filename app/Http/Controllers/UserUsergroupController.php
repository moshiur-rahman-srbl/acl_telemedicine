<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Usergroup;
use App\Models\UserUsergroup;
use Illuminate\Http\Request;
use Auth;

class UserUsergroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cmsInfo = [
            'moduleTitle' => __("Access Control"),
            'subModuleTitle' => __("User & User Group Association")
        ];
//        $SUPER_SUPER_ADMIN_ID = 1;
        $user = Auth::user();
        $company_id = $user->company_id;
        $usergroup = new Usergroup();
        $usergroups = $usergroup->getUsergroupsByCompanyId($company_id, $user->id);
//        dd($usergroups);
        return view('UserUsergroup.index', compact('cmsInfo', 'usergroups'));
    }

    public function ajaxGetData($id)
    {
        $user = Auth::user();
        $users = User::where('id', '!=', $user->id)->get();
        $userusergroups = UserUsergroup::where('usergroup_id', '=', $id)->get();

        $user_contents = view('UserUsergroup.userusergroupcontent', compact('users', 'userusergroups'))->render();
        $response['user_content'] = $user_contents;
        echo json_encode($response);
        exit;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function modify(Request $request)
    {

        $usergroup_id = $request->usergroup_id;
        $usergroup = UserUsergroup::where('usergroup_id', '=', $usergroup_id)->delete();

        if ($request->selected_users) {
            for ($i = 0; $i < sizeof($request->selected_users); $i++) {
                UserUsergroup::insert([
                    'usergroup_id' => $usergroup_id,
                    'user_id' => $request->selected_users[$i],
                    'company_id' => 0
                ]);
            }
        }

        flash(__('The record has been updated successfully!'), 'success');
        return back()->withInput();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
