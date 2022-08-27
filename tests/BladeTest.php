<?php

namespace  Yobidoyobi\Form\Tests;

use Yobidoyobi\Form\Facade\FormFactory;
use Yobidoyobi\Form\Tests\Types\UserFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BladeTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['router']->any('create', function () {
            $user = [];

            $form = FormFactory::create(UserFormType::class, $user)
                ->add('save', SubmitType::class, ['label' => 'Save user']);

            $form->handleRequest();

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    return 'valid';
                }

                return 'invalid';
            }

            return view('forms::create', compact('form'));
        });
    }

    /**
     * Test GET routes.
     *
     * @test
     */
    public function testInlineForm()
    {
        $crawler = $this->call('GET', 'create');

        $this->assertStringContainsString('<form name="user_form" method="post">', $crawler->getContent());
    }

    /**
     * Test GET routes.
     *
     * @test
     */
    public function testPostFormInvalid()
    {
        $crawler = $this->call('POST', 'create', [
            'user_form' => ['save' => true]
        ]);

        $this->assertEquals('invalid', $crawler->getContent());
    }

    /**
     * Test GET routes.
     *
     * @test
     */
    public function testPostForm()
    {
        $crawler = $this->call('POST', 'create', [
            'user_form' => [
                'name' => 'Barry',
                'email' => 'barryvdh@gmail.com',
                'save' => true
            ]
        ]);

        $this->assertEquals('valid', $crawler->getContent());
    }
}
