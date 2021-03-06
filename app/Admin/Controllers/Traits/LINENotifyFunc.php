<?php

namespace App\Admin\Controllers\Traits;

# MODELS
use App\Models\AdminUser;
use DB;

trait LINENotifyFunc
{
    /**
     * LINE-Notify 取消服務訊息通知
     *
     * @return void
     */
    public function lineNotifyCancel() {
        $admin = AdminUser::findOrFail(request()->get('id'));
        # 若使用者已連動則進行取消連動作業
        if (!empty($admin['line_notify_token'])) {
            $this->lineNotifyRevoke($admin);
            session()->flash('status', '解除連動');
        }
        return redirect()->route('admin.setting');
    }

    /**
     * 註冊服務訊息通知
     *
     * @param [type] $store_id
     * @param [type] $user_id
     * @return void
     */
    public function lineNotifyCallback() {
        $code = request()->get('code');
        $admin = AdminUser::findOrFail(request()->get('id'));
        $callbackUrl = route('admin-line-notify.callback', ['id' => $admin->id]);
        $admin->line_notify_auth_code = $code;
        $admin->save();
        ### LINE Access Token ###
        $this->getNotifyAccessToken($admin, $code, $callbackUrl);
        session()->flash('status', '連動完成!');
        return redirect()->route('admin.setting');
    }

    /**
     * 取消服務通知
     *
     * @param [type] $access_token
     * @return void
     */
    public function lineNotifyRevoke(AdminUser $admin) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://notify-api.line.me/api/revoke');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $admin['line_notify_token']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, true);
        /**
         * {"status":200,"message":"ok"}
         */
        if (in_array($output['status'],[200,401])) {
            $admin->line_notify_token = null;
            $admin->save();
        }
        return $output;
    }

    /**
     * 取得LINE Notify Access Token
     *
     * @param [type] $store_id
     * @param [type] $user_id
     * @param [type] $code
     * @param [type] $redirect_uri
     * @return void
     */
    private function getNotifyAccessToken(AdminUser $admin, $code, $redirect_uri) {
        // $admin = Administrator::where(['username'=>$username])->first();

        $apiUrl = "https://notify-bot.line.me/oauth/token";

        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri,
            'client_id' => config('app.line_notify_client_id'),
            'client_secret' => config('app.line_notify_client_secret')
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $output = curl_exec($ch);
        curl_close($ch);
        /**
         * {
         *      "status": 200,
         *      "message": "access_token is issued",
         *      "access_token": "7giNDfFWoAO1trYBA34YyfA6IZmazQoF4rmWSqrTtb3"
         *  }
         */
        $result = json_decode($output, true);
        $admin->refresh();
        $admin->line_notify_token = $result['access_token'];
        $admin->save();
    }
}
