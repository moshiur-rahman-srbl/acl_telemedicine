<?php
namespace App\Http\Controllers\Traits;
use App\Models\Merchant;
use App\Models\NotificationAutomation;
use App\Models\Profile;
use App\Models\UserAnnouncements;
use App\Models\WalletAnnouncement;
use common\integration\ManipulateDate;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Config;

trait AnnoucementTrait{
    private function notificationConfirmation($input, $path, $users)
    {
        if (count($users) > 0) {
            $notiauto = new NotificationAutomation();
            foreach ($users as $user) {
                if (!empty($user->email)) {
                    $data['sender_email'] = Config::get('constants.defines.MAIL_FROM_ADDRESS');
                    $data['receiver_email'] = $user->email;
                    $data['user_id'] = $user->id;
                    $data['language'] = $user->language;
                    $data['attachment'] = $path;

                    if ($data['language'] == 'en') {
                        $data['email_data']['mail_body'] = nl2br($input['mail_body'][0]);
                        $data['custom_subject'] = $data['subject'] = $input['mail_subject'][0];
                    } else {
                        $data['email_data']['mail_body'] = nl2br($input['mail_body'][1]);
                        $data['custom_subject'] = $data['subject'] = $input['mail_subject'][1];
                    }
                    $data['email_template'] = "walletannouncements.announcement";

                    if (!empty($input['announcement_date'])) {
                        $data['action_date'] = ManipulateDate::getDateFormat($input['announcement_date'], "Y-m-d H:i:s");
                    }

                    $notiauto->insertEntry($data, true);
                }
            }
        }
    }

    private function walletInsert($input, $authObj, $path, $isEmail, $isPanel, $imgPath, $announcement_end,$category=null,$receiversId=[])
    {
        if($category){
            $users = (new User())->getUsersByUserCategoryAndType($category, User::CUSTOMER);
        }else{
            $users = (new User())->getUsersById($receiversId);
        }

        if($isEmail) {
            $this->notificationConfirmation($input,$path,$users);
        }

        $inputData = [
            "admin_id" => $authObj->id,
            "admin_name" => $authObj->name,
            "user_type" => $input['user_type']??0,
            "user_category" => $category,
            "status" => $input['status'],
            "is_email" => $isEmail,
            "is_panel" => $isPanel,
            "en_subject" => $input['mail_subject'][0],
            "en_body" => nl2br($input['mail_body'][0]),
            "tr_subject" => $input['mail_subject'][1],
            "tr_body" => nl2br($input['mail_body'][1]),
            "email_attachment" => $path,
            "panel_attachment" => $imgPath,
            "announcement_date" => $input['announcement_date'] ? date("Y-m-d H:i:s", strtotime($input['announcement_date'])) : date("Y-m-d 00:00:00"),
            "announcement_end" => $announcement_end,
            "order" => $input['order']
        ];

        list($response, $result) = (new WalletAnnouncement())->insertData($inputData);

        if($isPanel && $response == 100 && !empty($result)) {
            $user_id_array = [];
            if (count($users) > 0) {
                foreach ($users as $user) {
                    array_push($user_id_array, $user->id);
                }
                (new UserAnnouncements())->insertByAnnouncementId($result->id, $user_id_array);
            }
        }
    }

    private function handleAjaxRequest(Request $request)
    {
        if($request->type==2){
            $users=(new Merchant())->getAllActiveMerchant();
        }else{
            $data['user_type']=$request->type;
            $users=(new Profile())->getBySearch($data,null);
        }

        if($users->isNotEmpty()){
            return view('partials.user_types',compact('users'))->render();
        }
    }
}
