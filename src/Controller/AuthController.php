<?php
declare(strict_types=1);

namespace App\Controller;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\Auth0;
use Cake\Http\Response;

/**
 * Auth Controller
 */
class AuthController extends AppController
{
    /**
     * @return \Auth0\SDK\Auth0
     */
    private function auth0(): Auth0
    {
        return new Auth0([
            'domain' => env('AUTH0_DOMAIN', ''),
            'client_id' => env('AUTH0_CLIENT_ID', ''),
            'client_secret' => env('AUTH0_CLIENT_SECRET', ''),
            'redirect_uri' => env('AUTH0_CALLBACK_URL', ''),

            // refresh token が必要な場合は、scope へ offline_access を追加する
            // @see https://auth0.com/docs/tokens/guides/get-refresh-tokens
            'scope' => 'openid profile email',
            // 'scope' => 'openid profile email offline_access',

            // exchange (code から access token を取得) 後、ユーザー情報を取得する場合は true とする
            // access token と一緒に返却される id token(JWT) を decode したユーザー情報でよければ false (デフォルト) とする
            // 'skip_userinfo' => false,

            // access token を Store (Session情報) へ保持しない場合は false とする
            // 'persist_user' => false,

            // access token を Store (Session情報) へ保持する場合は true とする
            'persist_access_token' => true,

            // refresh token を Store (Session情報) へ保持する場合は true とする
            // 'persist_refresh_token' => true,

            // id token を Store (Session情報) へ保持する場合は true とする
            // 'persist_id_token' => true,
        ]);
    }

    /**
     * @return \Cake\Http\Response|null
     */
    public function login(): ?Response
    {
        // Session を破棄して、Auth0のユーザー情報を空にする。
        // そうしないと、Auth0->exchange() で
        // Can't initialize a new session while there is one active session already
        // が発生する。
        $this->getRequest()->getSession()->destroy();

        $auth0 = $this->auth0();

        $loginUrl = $auth0->getLoginUrl();

        return $this->redirect($loginUrl);
    }

    /**
     * @return \Cake\Http\Response|null
     */
    public function logout(): ?Response
    {
        $auth0 = $this->auth0();

        $auth0->logout();

        /** @var string $domain */
        $domain = env('AUTH0_DOMAIN', '');
        /** @var string $clientId */
        $clientId = env('AUTH0_CLIENT_ID', '');
        $authApi = new Authentication($domain, $clientId);

        /** @var string $returnTo */
        $returnTo = env('AUTH0_LOGOUT_URL', '');
        $logoutUrl = $authApi->get_logout_link($returnTo, $clientId);

        return $this->redirect($logoutUrl);
    }

    /**
     * @return \Cake\Http\Response|null
     */
    public function callback(): ?Response
    {
        $auth0 = $this->auth0();

        $auth0->exchange();

        return $this->render();
    }

    /**
     * @return \Cake\Http\Response|null
     */
    public function user(): ?Response
    {
        $auth0 = $this->auth0();

        /** @var string $domain */
        $domain = env('AUTH0_DOMAIN', '');
        /** @var string $clientId */
        $clientId = env('AUTH0_CLIENT_ID', '');
        $authApi = new Authentication($domain, $clientId);

        /** @var string $accessToken */
        $accessToken = $auth0->getAccessToken();
        $user = $authApi->userinfo($accessToken);

        debug($user);

        return $this->render();
    }

    /**
     * @return \Cake\Http\Response|null
     */
    public function cache(): ?Response
    {
        $auth0 = $this->auth0();

        debug($auth0->getUser());
        debug($auth0->getAccessToken());
        debug($auth0->getIdToken());
        debug($auth0->getRefreshToken());

        return $this->render();
    }
}
