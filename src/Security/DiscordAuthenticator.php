<?php

namespace App\Security;

use App\Entity\Core\User\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use UnexpectedValueException;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

class DiscordAuthenticator extends SocialAuthenticator
{
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var JWTTokenManagerInterface
     */
    private $tokenManager;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, JWTTokenManagerInterface $tokenManager)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;
    }

    /**
     * Returns a response that directs the user to authenticate.
     * This is called when an anonymous request accesses a resource that
     * requires authentication. The job of this method is to return some
     * response that "helps" the user start into the authentication process.
     * Examples:
     * - For a form login, you might redirect to the login page
     *     return new RedirectResponse('/login');
     * - For an API token authentication system, you return a 401 response
     *     return new Response('Auth header required', 401);.
     *
     * @param Request                 $request       The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Does the authenticator support the given Request?
     * If this returns false, the authenticator will be skipped.
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return ' app_core_oauth_discord_connectdiscordcheck' === $request->attributes->get('_route');
    }

    /**
     * Get the authentication credentials from the request and return them
     * as any type (e.g. an associate array).
     * Whatever value you return here will be passed to getUser() and checkCredentials()
     * For example, for a form login, you might:
     *      return array(
     *          'username' => $request->request->get('_username'),
     *          'password' => $request->request->get('_password'),
     *      );
     * Or for an API token that's on a header, you might use:
     *      return array('api_key' => $request->headers->get('X-API-TOKEN'));.
     *
     * @return mixed Any non-null value
     *
     * @throws UnexpectedValueException If null is returned
     */
    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getDiscordClient());
    }

    /**
     * Return a UserInterface object based on the credentials.
     * The *credentials* are the return value from getCredentials()
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed $credentials
     *
     * @return UserInterface|null
     *
     * @throws AuthenticationException
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var DiscordResourceOwner $discordUser */
        $discordUser = $this->getDiscordClient()->fetchUserFromToken($credentials);

        //user already linked discord
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['discordId' => $discordUser->getId()]);
        if ($user) {
            return $user;
        }

        //user exists without having linked
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $discordUser->getEmail()]);
        if ($user) {
            $user->setDiscordId($discordUser->getId());
            $this->entityManager->flush();

            return $user;
        }

        $user = new User();
        $user->setEmail($discordUser->getEmail());
        $user->setUsername($discordUser->getUsername());
        $user->setDiscordId($discordUser->getId());
        $user->addRole(User::ROLE_DEFAULT);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 403 response.
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse($exception->getMessageData(), Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication executed and was successful!
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the last page they visited.
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     *
     * @param string $providerKey The provider (i.e. firewall) key
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $state = json_decode($request->get('state'));
        $authToken = $this->tokenManager->create($token->getUser());

        $response = new RedirectResponse($state->opener, 302, [
            'X-AUTH-TOKEN' => $authToken,
        ]);

        $response->headers->setCookie(new Cookie('auth-gg', $authToken, 0, '/', null, false, false));

        return $response;
    }

    private function getDiscordClient()
    {
        return $this->clientRegistry->getClient('discord');
    }
}
