<?php

/**
 * SecurityController Test
 */

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * SecurityControllerTest
 * @coversDefaultClass \AppBundle\Controller\SecurityController
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * @var Client
     * @access private
     */
    private $client;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->client = self::createClient();
    }

    /**
     * Test Registration method of SecurityController
     * @access public
     * @covers ::registrationAction
     *
     * @return void
     */
    public function testRegistration()
    {
        $crawler = $this->client->request('GET', '/registration');

        $form = $crawler->selectButton('Create an account')->form();
        $form['appbundle_user[username]'] = 'Zebulon';
        $form['appbundle_user[email]'] = 'zebulontest@yahoo.fr';
        $form['appbundle_user[password]'] = 'averygreatepassword45';
        $form['appbundle_user[name]'] = 'Example';
        $form['appbundle_user[firstName]'] = 'Jean';
        $form['appbundle_user[image][file]']->upload(
            new UploadedFile(
                __DIR__.'/../uploads/userimage.png',
                'userimage.png',
                'image/png',
                null,
                null,
                true
            )
        );

        $this->client->submit($form);
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test the path to registration (Home - Registration)
     * @access public
     *
     * @return void
     */
    public function testPathToRegistration()
    {
        $crawler = $this->client->request('GET', '/');

        $link = $crawler->selectLink('Sign in')->link();
        $crawler = $this->client->click($link);

        $this->assertSame('Registration', $crawler->filter('h1')->text());
    }

    /**
     * Test Registration method of SecurityController with bad values
     * @access public
     * @param string $username
     * @param string $email
     * @param string $pass
     * @param string $name
     * @param string $firstName
     * @param UploadedFile $image
     * @param int $result
     * @covers ::registrationAction
     * @dataProvider valuesRegistrationForm
     *
     * @return void
     */
    public function testRegistrationWithBadValues($username, $email, $pass, $name, $firstName, $image, $result)
    {
        $crawler = $this->client->request('GET', '/registration');

        $form = $crawler->selectButton('Create an account')->form();
        $form['appbundle_user[username]'] = $username;
        $form['appbundle_user[email]'] = $email;
        $form['appbundle_user[password]'] = $pass;
        $form['appbundle_user[name]'] = $name;
        $form['appbundle_user[firstName]'] = $firstName;
        $form['appbundle_user[image][file]']->upload($image);

        $crawler = $this->client->submit($form);

        $this->assertSame($result, $crawler->filter('span.form-error-message')->count());     
    }

    /**
     * Test Valid Registration method of SecurityController
     * @access public
     * @covers ::validRegistrationAction
     *
     * @return void
     */
    public function testValidRegistration()
    {
        $this->client->request(
            'GET',
            '/registration/validation/c15b26a3d01aa113ed235d570ca43d621a552be7c9821aab8238a40f40b53e686689559629535112'
        );

        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('div:contains("Valid registration !")')->count());
    }

    /**
     * Test validRegistration method of SecurityController with bad token
     * @access public
     * @covers ::validRegistrationAction
     *
     * @return void
     */
    public function testValidRegistrationWithBadToken()
    {
        $this->client->request('GET', '/registration/validation/rdfpdfpfd58978512');

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test the path to login (Home - Login)
     * @access public
     *
     * @return void
     */
    public function testPathToLogin()
    {
        $crawler = $this->client->request('GET', '/');

        $link = $crawler->selectLink('Sign up')->link();
        $crawler = $this->client->click($link);

        $this->assertSame('Login', $crawler->filter('h1')->text());
    }

    /**
     * Test Login method of SecurityController
     * @access public
     * @covers ::loginAction
     *
     * @return void
     */
    public function testLogin()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'EnabledUser';
        $form['_password'] = 'verystrongpassword123';

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        
        $this->assertSame(1, $crawler->filter('a:contains("Logout")')->count());
    }

    /**
     * Test Registration method of SecurityController with bad values
     * @access public
     * @param string $username
     * @param string $pass
     * @covers ::loginAction
     * @dataProvider valuesLoginForm
     *
     * @return void
     */
    public function testLoginWithBadValues($username, $pass)
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = $username;
        $form['_password'] = $pass;

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        
        $this->assertSame(1, $crawler->filter('div[class="alert alert-danger"]')->count());    
    }

    /**
     * Test Logout method of SecurityController
     * @access public
     * @covers ::logoutAction
     *
     * @return void
     */
    public function testLogout()
    {
        $this->logIn();
        $this->client->request('GET', '/logout');
        $crawler = $this->client->followRedirect();

        $this->assertSame(0, $crawler->filter('a:contains("Logout")')->count());
    }

    /**
     * Log user
     * @access private
     *
     * @return void
     */
    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken('simpleTest', null, $firewallName, array('ROLE_MEMBER'));
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * Test the user access of url when he's logged
     * @access public
     * @param string $url
     * @dataProvider urlAuthUserCantAccess
     *
     * @return void
     */
    public function testAuthUserCantAccess($url)
    {
        $this->login();

        $this->client->request('GET', $url);
        $crawler = $this->client->followRedirect();

        $this->assertSame(' SnowTricks - Home', $crawler->filter('title')->text());
    }

    /**
     * User can't access that url when he's logged
     * @access public
     *
     * @return array
     */
    public function urlAuthUserCantAccess()
    {
        return [
            [
                '/registration'
            ],
            [
                '/login'
            ],
            [
                '/registration/validation/c15b26a3d01aa113ed2fcd5e52a43d621a552be7c9821aab8238a40f40b53e686689559629512869'
            ]
        ];
    }

    /**
     * Form values
     * @access public
     *
     * @return array
     */
    public function valuesRegistrationForm()
    {
        return [
            [
                '',
                '',
                '',
                '',
                '',
                '',
                6
            ],
            [
                '!',
                '!',
                'sd',
                '!',
                '!',
                new UploadedFile(
                    __DIR__.'/../uploads/badFormat.gif',
                    'badFormat.gif',
                    'image/gif',
                    null,
                    null,
                    true
                ),
                11
            ],
            [
                'azertyuiopqsdfghjklmwxcvbnhjghr',
                'azertyuiopqsdfghjklmwxcvbnhjgrazertyuiopqsdfghjklmwxcvbnhjgr@yahooo.com',
                'azertyuiopqsdfghjklmwxcvbnhjgr56951fslepfmvjbfz2c',
                'azertyuiopqsdfghjklmwxcvbnhjgr-hhhmmhlkvg',
                'azertyuiopqsdfghjklmwxcvbnhjgr-hhhmmhlkvg',
                new UploadedFile(
                    __DIR__.'/../uploads/bigFile.jpg',
                    'bigFile.jpg',
                    'image/jpeg',
                    null,
                    null,
                    true
                ),
                6
            ]
        ];
    }

    /**
     * Form values
     * @access public
     *
     * @return array
     */
    public function valuesLoginForm()
    {
        return [
            [
                '',
                ''
            ],
            [
                'InactiveUser',
                'verystrongpassword1222'
            ],
            [
                'EnabledUser',
                'badPass'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->client = null;
    }
}