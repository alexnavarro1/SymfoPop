<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

// Aquest és el meu propi mecanisme fet a mà com es requereix en exercicis generals que mira de deixar un control segur personal del login i la validació real al lloc idoni.
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    // Afegeixo trait per aconseguir que torni a l'usuari on havia d'anar si no passa cap altre camí
    use TargetPathTrait;

    // Defineix la pròpia base arrel principal de rutes Login pròpies de l'App (app_login).
    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    // Gestiono primer de tot on arriben totes les meves dades com contrasenya
    public function authenticate(Request $request): Passport
    {
        // Només acceptaré correu electrònic en la petició que rebuda
        $email = $request->request->get('email', '');

        // Mantindré dins de la memòria d'aquest inici un ultim email enviat en defecte al que fa d'accés
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Envío els bitllets bàsics que farà que en funcioni almenys dos de d'autenticadors propis amb symfony! "L'identitat", "passwordSecret", "El CSRF obligatori", "Botó Manteniment Checkbox".
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    // Un cop estic garantit d'èxit de loguejar faig:
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Torno on estiguviessis abans... 
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // ... O directament que vagis a index al acabar el login! Molt simple
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    // Quan no passi en primera instància et llançaré constantment contra aquesta vista! 
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
