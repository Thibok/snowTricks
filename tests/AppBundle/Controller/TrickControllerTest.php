<?php

/**
 * TrickController Test
 */

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * TrickControllerTest
 * @coversDefaultClass \AppBundle\Controller\TrickController
 */
class TrickControllerTest extends WebTestCase
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
     * Test addAction method of TrickController
     * @access public
     * @covers ::addAction
     *
     * @return void
     */
    public function testAddTrick()
    {
        $fileDir = __DIR__.'/../uploads/';
        copy($fileDir.'trick.jpg', $fileDir.'trick-copy.jpg');
        copy($fileDir.'snow.png', $fileDir.'snow-copy.png');

        $this->logIn();
        $crawler = $this->client->request('GET', '/tricks/add');

        $form = $crawler->selectButton('Save')->form();
        $form['appbundle_trick[name]'] = 'Trick test';
        $form['appbundle_trick[description]'] = 'A short description !';
        $selectOptions = $form['appbundle_trick[category]']->availableOptionValues();
        $form['appbundle_trick[category]']->select($selectOptions[1]);
        $values = $form->getPhpValues();
        $values['appbundle_trick']['images'][0]['file'] = new UploadedFile(
            $fileDir.'trick-copy.jpg',
            'trick-copy.jpg',
            'image/jpg',
            null,
            null,
            true
        );
        $values['appbundle_trick']['images'][1]['file'] = new UploadedFile(
            $fileDir.'snow-copy.png',
            'snow-copy.png',
            'image/png',
            null,
            null,
            true
        );

        $values['appbundle_trick']['videos'][0]['url'] = 'https://youtu.be/VZ4teZHfpkc';
        $values['appbundle_trick']['videos'][1]['url'] = 'https://www.youtube.com/watch?v=VZ4teZHfpkc';
        $values['appbundle_trick']['videos'][2]['url'] = 'https://www.youtube.com/embed/VZ4teZHfpkc';
        $values['appbundle_trick']['videos'][3]['url'] = 'https://www.dailymotion.com/video/x14wofv';
        $values['appbundle_trick']['videos'][4]['url'] = 'https://dai.ly/x14wofv';
        $values['appbundle_trick']['videos'][5]['url'] = 'https://www.dailymotion.com/embed/video/x14wofv';
        $values['appbundle_trick']['videos'][6]['url'] = 'https://vimeo.com/9636197';
        $values['appbundle_trick']['videos'][7]['url'] = 'https://player.vimeo.com/video/9636197';

        $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('div.flash-notice')->count());
    }

    /**
     * Test editAction method of TrickController
     * @access public
     * @covers ::editAction
     *
     * @return void
     */
    public function testUpdateTrick()
    {
        $fileDir = __DIR__.'/../uploads/';
        copy($fileDir.'snow.png', $fileDir.'snow-copy-1.png');

        $this->logIn();
        $crawler = $this->client->request('GET', '/tricks/details/a-very-good-trick/update');

        $form = $crawler->selectButton('Edit')->form();
        $form['appbundle_trick[name]'] = 'New name';
        $form['appbundle_trick[description]'] = 'New description !';
        $form['appbundle_trick[videos][0][url]'] = 'https://www.youtube.com/watch?v=tHHxTHZwFUw';
        $form['appbundle_trick[videos][1][url]'] = 'https://www.dailymotion.com/video/xnltrc';
        $selectOptions = $form['appbundle_trick[category]']->availableOptionValues();
        $form['appbundle_trick[category]']->select($selectOptions[4]);
        $form['appbundle_trick[images][0][file]']->upload(
            new UploadedFile(
                __DIR__.'/../uploads/trick.jpg',
                'trick.jpg',
                'image/jpg',
                null,
                null,
                true
            )
        );
        $values = $form->getPhpValues();
        unset($values['appbundle_trick']['videos'][2]['url']);
        unset($values['appbundle_trick']['images'][1]['file']);

        $values['appbundle_trick']['videos'][2]['url'] = 'https://dai.ly/xx0pxj';
        $values['appbundle_trick']['videos'][3]['url'] = 'https://youtu.be/397Z2HrHn-4';
        $values['appbundle_trick']['videos'][4]['url'] = 'https://www.youtube.com/embed/K-RKP3BizWM';
        $values['appbundle_trick']['videos'][5]['url'] = 'https://www.dailymotion.com/embed/video/xx0pxj';
        $values['appbundle_trick']['videos'][6]['url'] = 'https://vimeo.com/14050350';
        $values['appbundle_trick']['videos'][7]['url'] = 'https://player.vimeo.com/video/4806901';

        $values['appbundle_trick']['images'][3]['file'] = new UploadedFile(
            $fileDir.'snow-copy-1.png',
            'snow-copy-1.png',
            'image/png',
            null,
            null,
            true
        );

        $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        $crawler = $this->client->followRedirect();
        $this->assertSame(1, $crawler->filter('div.flash-notice')->count());

        $crawler = $this->client->request('GET', '/tricks/details/new-name/update');
        $form = $crawler->selectButton('Edit')->form();
        $name = $form->get('appbundle_trick[name]')->getValue();
        $description = $form->get('appbundle_trick[description]')->getValue();
        $category = $crawler->filter('option[selected]')->text();

        $this->assertSame(2, $crawler->filter('#trickImages > input')->count());
        $this->assertSame(8, $crawler->filter('#trickVideos > input')->count());
        $this->assertSame('New name', $name);
        $this->assertSame('New description !', $description);
        $this->assertSame('One foot tricks', $category);
    }

    /**
     * Test addAction method of TrickController with bad values
     * @access public
     * @param string $name
     * @param string $description
     * @param UploadedFile $image
     * @param string $video
     * @param int $result
     * @covers ::addAction
     * @dataProvider valuesTrickForm
     * 
     * @return void
     */
    public function testAddTrickWithBadValues($name, $description, $image, $video, $result)
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/tricks/add');
        $form = $crawler->selectButton('Save')->form();

        $form['appbundle_trick[name]'] = $name;
        $form['appbundle_trick[description]'] = $description;
        $values = $form->getPhpValues();

        if ($image != '') {
            $values['appbundle_trick']['images'][0]['file'] = $image;
        }

        $values['appbundle_trick']['videos'][0]['url'] = $video;

        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());

        $this->assertSame($result, $crawler->filter('span.form-error-message')->count());
    }

    /**
     * Test editAction method of TrickController with bad values
     * @access public
     * @param string $name
     * @param string $description
     * @param UploadedFile $image
     * @param string $video
     * @param int $result
     * @covers ::editAction
     * @dataProvider valuesTrickForm
     * 
     * @return void
     */
    public function testUpdateTrickWithBadValues($name, $description, $image, $video, $result)
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/tricks/details/a-simple-trick/update');
        $form = $crawler->selectButton('Edit')->form();

        $form['appbundle_trick[name]'] = $name;
        $form['appbundle_trick[description]'] = $description;
        $values = $form->getPhpValues();

        if ($image != '') {
            $values['appbundle_trick']['images'][0]['file'] = $image;
        }

        $values['appbundle_trick']['videos'][0]['url'] = $video;

        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());

        $this->assertSame($result, $crawler->filter('span.form-error-message')->count());
    }

    /**
     * Form values
     * @access public
     *
     * @return array
     */
    public function valuesTrickForm()
    {
        return [
            [
                '',
                '',
                '',
                '',
                2
            ],
            [
                'n',
                '<bad description>',
                new UploadedFile(
                    __DIR__.'/../uploads/badFormat.gif',
                    'badFormat.gif',
                    'image/gif',
                    null,
                    null,
                    true
                ),
                'http://youtu.be/VZ4teZHfpkc',
                6
            ],
            [
                'a very very very very very very very very long title for a trick name',
                'baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad
                baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa description',
                new UploadedFile(
                    __DIR__.'/../uploads/bigFile.jpg',
                    'bigFile.jpg',
                    'image/jpg',
                    null,
                    null,
                    true
                ),
                'https://youtu.be/VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffe
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu
                VZ4teZHfpkcmcldofjnzhhzhdbsjqkqlzppzdzpdzpdpzdmpzpkgrjigirgirjgrg662233efefeffefefefeeffevmbkbzjefeu',
                7
            ]
        ];
    }

    /**
     * Test path to access add trick page
     * @access public
     *
     * @return void
     */
    public function testPathToAddTrick()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/');

        $link = $crawler->selectLink('Add trick')->link();
        $crawler = $this->client->click($link);

        $this->assertSame(' SnowTricks - Add trick', $crawler->filter('title')->text());
    }

    /**
     * Test path to access edit trick page
     * @access public
     *
     * @return void
     */
    public function testPathToEditTrick()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/');

        $link = $crawler->filter('a[href="/tricks/details/a-simple-trick/update"]')->link();
        $crawler = $this->client->click($link);

        $this->assertSame(' SnowTricks - Edit A simple trick', $crawler->filter('title')->text());
    }

    /**
     * Test the user can't access url if he's not logged
     * @access public
     * @param string $url
     * @dataProvider urlNoAuthUserCantAccess
     *
     * @return void
     */
    public function testNoAuthUserCantAccess($url)
    {
        $this->client->request('GET', $url);
        $crawler = $this->client->followRedirect();

        $this->assertSame('Login', $crawler->filter('h1')->text());
    }

    /**
     * Url values
     * @access public
     *
     * @return array
     */
    public function urlNoAuthUserCantAccess()
    {
        return [
            [
                '/tricks/add'
            ],
            [
                '/tricks/details/a-simple-trick/update'
            ]
        ];
    }

    /**
     * Test if user can't access comment form if is not logged
     * @access public
     *
     * @return void
     */
    public function testUserCantAccessCommentFormIfHeIsNotAuth()
    {
        $crawler = $this->client->request('GET', '/tricks/details/a-simple-trick');
        $this->assertSame(0, $crawler->filter('form')->count());
    }

    /**
     * Test if user can access comment form if is logged
     * @access public
     *
     * @return void
     */
    public function testUserCanAccessCommentFormIfHeIsAuth()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/tricks/details/a-simple-trick');
        $this->assertSame(1, $crawler->filter('form')->count());
    }

    /**
     * Test the path to trick details (Home - A simple trick details)
     * @access public
     *
     * @return void
     */
    public function testPathToViewTrick()
    {
        $crawler = $this->client->request('GET', '/');

        $link = $crawler->filter('a[href="/tricks/details/a-simple-trick"]')->link();
        $crawler = $this->client->click($link);

        $this->assertSame(' SnowTricks - A simple trick details', $crawler->filter('title')->text());
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
        $em = $this->client->getContainer()->get('doctrine')->getManager(); 
        $user = $em->getRepository('AppBundle:User')->findOneByUsername('EnabledUser'); 

        $token = new UsernamePasswordToken($user, $user->getPassword(), $firewallName, $user->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->client = null;
    }
}