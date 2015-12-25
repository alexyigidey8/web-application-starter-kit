<?php

namespace Application\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Application\Form\Type\User\RegisterType;
use Application\Form\Type\User\ResetPasswordType;
use Application\Entity\UserEntity;

/**
 * @author Borut Balažek <bobalazek124@gmail.com>
 */
class MembersAreaController
{
    public function indexAction(Request $request, Application $app)
    {
        return new Response(
            $app['twig']->render(
                'contents/members-area/index.html.twig'
            )
        );
    }

    public function loginAction(Request $request, Application $app)
    {
        $data = array(
            'lastUsername' => $app['session']->get('_security.last_username'),
            'lastError' => $app['security.last_error']($app['request']),
            'csrfToken' => $app['form.csrf_provider']->getToken('authenticate'), // The intention MUST be "authenticate"
        );

        return new Response(
            $app['twig']->render(
                'contents/members-area/login.html.twig',
                $data
            )
        );
    }

    public function logoutAction(Request $request, Application $app)
    {
        return new Response(
            $app['twig']->render(
                'contents/members-area/logout.html.twig'
            )
        );
    }

    public function registerAction(Request $request, Application $app)
    {
        $data = array();

        $code = $request->query->has('code')
            ? $request->query->get('code')
            : false
        ;
        $action = $code
            ? 'confirm'
            : 'register'
        ;
        $alert = false;
        $alertMessage = '';

        $form = $app['form.factory']->create(
            new RegisterType(),
            new UserEntity()
        );

        if ($action == 'confirm') {
            $userEntity = $app['orm.em']
                ->getRepository('Application\Entity\UserEntity')
                ->findOneByActivationCode($code)
            ;

            if ($userEntity) {
                $userEntity
                    ->setActivationCode(null)
                    ->enable()
                ;

                $app['orm.em']->merge($userEntity);
                $app['orm.em']->flush();

                $app['application.mailer']
                    ->swiftMessageInitializeAndSend(array(
                        'subject' => $app['name'].' - '.$app['translator']->trans('Welcome'),
                        'to' => array( $userEntity->getEmail() ),
                        'body' => 'emails/users/register-welcome.html.twig',
                        'type' => 'user.register.welcome',
                        'templateData' => array(
                            'user' => $userEntity,
                        ),
                    ))
                ;

                $alert = 'success';
                $alertMessage = 'members-area.register.confirm.successText';
            } else {
                $alert = 'danger';
                $alertMessage = 'members-area.register.confirm.activationCodeNotFound';
            }
        } else {
            if (
                $request->getMethod() == 'POST' &&
                $app['userSystemOptions']['registrationEnabled']
            ) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $userEntity = $form->getData();

                    $userEntity->setPlainPassword(
                        $userEntity->getPlainPassword(),
                        $app['security.encoder_factory']
                    );

                    $app['application.mailer']
                        ->swiftMessageInitializeAndSend(array(
                            'subject' => $app['name'].' - '.$app['translator']->trans('Registration'),
                            'to' => array( $userEntity->getEmail() ),
                            'body' => 'emails/users/register.html.twig',
                            'type' => 'user.register',
                            'templateData' => array(
                                'user' => $userEntity,
                            ),
                        ))
                    ;

                    $app['orm.em']->persist($userEntity);
                    $app['orm.em']->flush();

                    $alert = 'success';
                    $alertMessage = 'members-area.register.successText';
                }
            }
        }

        $data['form'] = $form->createView();
        $data['alert'] = $alert;
        $data['alertMessage'] = $alertMessage;

        return new Response(
            $app['twig']->render(
                'contents/members-area/register.html.twig',
                $data
            )
        );
    }

    public function resetPasswordAction(Request $request, Application $app)
    {
        $data = array();

        $code = $request->query->has('code')
            ? $request->query->get('code')
            : false
        ;
        $action = $code
            ? 'reset'
            : 'request'
        ;
        $alert = false;
        $alertMessage = '';

        $form = $app['form.factory']->create(
            new ResetPasswordType($action),
            new UserEntity()
        );

        if ($action == 'reset') {
            $userEntity = $app['orm.em']
                ->getRepository('Application\Entity\UserEntity')
                ->findOneByResetPasswordCode($code)
            ;

            if ($userEntity) {
                if ($request->getMethod() == 'POST') {
                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $temporaryUserEntity = $form->getData();

                        $userEntity
                            ->setResetPasswordCode(null)
                            ->setPlainPassword(
                                $temporaryUserEntity->getPlainPassword(),
                                $app['security.encoder_factory']
                            )
                        ;

                        $app['orm.em']->persist($userEntity);
                        $app['orm.em']->flush();
                        
                        $app['application.mailer']
                            ->swiftMessageInitializeAndSend(array(
                                'subject' => $app['name'].' - '.$app['translator']->trans('Reset Password Confirmation'),
                                'to' => array(
                                    $userEntity->getEmail() => $userEntity->getProfile()->getFullName(),
                                ),
                                'body' => 'emails/users/reset-password-confirmation.html.twig',
                                'templateData' => array(
                                    'user' => $userEntity,
                                ),
                            ))
                        ;

                        $alert = 'success';
                        $alertMessage = 'members-area.request-password.reset.success';
                    }
                }
            } else {
                $alert = 'danger';
                $alertMessage = 'members-area.request-password.reset.resetPasswordCodeNotFound';
            }
        } else {
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $temporaryUserEntity = $form->getData();

                    $userEntity = $app['orm.em']
                        ->getRepository('Application\Entity\UserEntity')
                        ->findOneByEmail(
                            $temporaryUserEntity->getEmail()
                        );

                    if ($userEntity) {
                        $app['application.mailer']
                            ->swiftMessageInitializeAndSend(array(
                                'subject' => $app['name'].' - '.$app['translator']->trans('Reset password'),
                                'to' => array( $userEntity->getEmail() ),
                                'body' => 'emails/users/reset-password.html.twig',
                                'type' => 'user.reset_password',
                                'templateData' => array(
                                    'user' => $userEntity,
                                ),
                            ))
                        ;

                        $alert = 'success';
                        $alertMessage = 'requestPassword.request.success';
                    } else {
                        $alert = 'danger';
                        $alertMessage = 'requestPassword.request.emailNotFound';
                    }
                }
            }
        }

        $data['code'] = $code;
        $data['action'] = $action;
        $data['form'] = $form->createView();
        $data['alert'] = $alert;
        $data['alertMessage'] = $alertMessage;

        return new Response(
            $app['twig']->render(
                'contents/members-area/reset-password.html.twig',
                $data
            )
        );
    }
}
