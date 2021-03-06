<?php

/**
 * Controller of Security package
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Token;
use AppBundle\Form\UserType;
use Doctrine\ORM\ORMException;
use AppBundle\Event\UserEvents;
use AppBundle\Form\UserForgotPassType;
use AppBundle\Handler\ResetPassHandler;
use AppBundle\Event\UserPostForgotEvent;
use AppBundle\ParamChecker\CaptchaChecker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Security Controller
 */
class SecurityController extends Controller
{
    /**
     * Registration
     * @access public
     * @param Request $request
     * @param CaptchaChecker $captchaChecker
     * @Route("/registration", name="st_registration")
     * 
     * @return mixed Response | RedirectResponse
     */
    public function registrationAction(Request $request, CaptchaChecker $captchaChecker)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('st_index');
        }

        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $captchaChecker->check() && $form->isValid()) {
            $token = new Token;
            $token->setType('registration');
            $user->setToken($token);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
                
            try {
                $manager->flush();
                $this->addFlash('notice', 'Registration Success !');    
                $this->addFlash('notice', 'A confirmation link has been sent to you by email');
            } catch(ORMException $e) {
                $this->addFlash('error', 'An error has occurred');
            }

            return $this->redirectToRoute('st_index');
        }

        return $this->render('security/registration.html.twig', array('form' => $form->createView()));
    }

    /**
     * Validation Registration
     * @access public
     * @param string $tokenCode
     * @Route("/registration/validation/{tokenCode}", name="st_valid_registration", requirements={"tokenCode"="[a-z0-9]{80}"})
     * 
     * @return RedirectResponse
     */
    public function validRegistrationAction($tokenCode)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('st_index');
        }

        $manager = $this->getDoctrine()->getManager();
        $token = $manager->getRepository(Token::class)->getTokenWithUser($tokenCode);
        $actualDate = new \DateTime;

        if ($token == null || $token->getType() != 'registration' || $token->getExpirationDate() <= $actualDate) {
            throw $this->createNotFoundException();
        }
        
        $user = $token->getUser();
        $user->setIsActive(true);
        $manager->remove($token);

        try {
            $manager->flush();
            $this->addFlash('notice', 'Valid registration !');
        } catch(ORMException $e) {
            $this->addFlash('error', 'An error has occurred');
        }

        return $this->redirectToRoute('st_index');
    }

    /**
     * Login
     * @access public
     * @param AuthenticationUtils $authenticationUtils
     * @Route("/login", name="st_login")
     * 
     * @return mixed Response | RedirectResponse
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('st_index');
        }

        return $this->render('security/login.html.twig', array(
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ));
    }

    /**
     * Logout
     * @access public
     * @Route("/logout", name="st_logout")
     * 
     * @return void
     */
    public function logoutAction() 
    {
    
    }

    /**
     * Forgot pass
     * @access public
     * @param Request $request
     * @param CaptchaChecker $captchaChecker
     * @param EventDispatcherInterface $dispatcher
     * @Route("/forgot_password", name="st_forgot_pass")
     * 
     * @return mixed Response | RedirectResponse
     */
    public function forgotPassAction(Request $request, CaptchaChecker $captchaChecker, EventDispatcherInterface $dispatcher)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('st_index');
        }

        $form = $this->createForm(UserForgotPassType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $captchaChecker->check() && $form->isValid()) {
            $data = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $user = $manager->getRepository(User::class)->findOneByUsername($data['username']);
            $token = new Token;
            $token->setType('reset-pass');
            $user->setToken($token);
            $manager->persist($token);

            try {
                $manager->flush();
                $event = new UserPostForgotEvent($user);
                $dispatcher->dispatch(UserEvents::POST_FORGOT, $event);
                $this->addFlash('notice', 'A reset password link has been sent to you by email');
            } catch(ORMException $e) {
                $this->addFlash('error', 'An error has occurred');
            }

            return $this->redirectToRoute('st_index');
        }

        return $this->render('security/forgot_pass.html.twig', array('form' => $form->createView()));
    }

    /**
     * Reset pass
     * @access public
     * @param Request $request
     * @param string $tokenCode
     * @param ResetPassHandler $handler
     * @Route("/reset_password/{tokenCode}", name="st_reset_pass", requirements={"tokenCode"="[a-z0-9]{80}"})
     * 
     * @return mixed Response | RedirectResponse 
     */
    public function resetPassAction(Request $request, $tokenCode, ResetPassHandler $handler)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('st_index');
        }

        $manager = $this->getDoctrine()->getManager();
        $token = $manager->getRepository(Token::class)->getTokenWithUser($tokenCode);
        $actualDate = new \DateTime;

        if ($token == null || $token->getType() != 'reset-pass' || $token->getExpirationDate() <= $actualDate) {
            throw $this->createNotFoundException();
        }
        
        $user = $token->getUser();
        $form = $handler->createForm($user);

        if ($handler->validateForm($form, $request)) {
            
            $handler->handleDataToUser($form, $user);
            $manager->remove($token);

            try {
                $manager->flush();
                $this->addFlash('notice', 'The password has been updated !');
            } catch(ORMException $e) {
                $this->addFlash('error', 'An error has occurred');
            }

            return $this->redirectToRoute('st_index');
        }

        return $this->render('security/reset_pass.html.twig', array('form' => $form->createView()));
    }
}
