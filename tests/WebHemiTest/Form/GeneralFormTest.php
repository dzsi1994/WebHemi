<?php
/**
 * WebHemi.
 *
 * PHP version 7.0
 *
 * @copyright 2012 - 2017 Gixx-web (http://www.gixx-web.com)
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link      http://www.gixx-web.com
 */
namespace WebHemiTest\Form;

use WebHemi\Form\Element\NestedElementInterface;
use WebHemi\Form\Element\Web;
use WebHemiTest\AssertTrait;
use WebHemiTest\InvokePrivateMethodTrait;
use WebHemiTest\Fixtures\TestWebForm;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class GeneralFormTest.
 */
class GeneralFormTest extends TestCase
{
    use AssertTrait;
    use InvokePrivateMethodTrait;

    /**
     * Tests constructor.
     */
    public function testConstructor()
    {
        $testForm = new TestWebForm(new Web\FormElementContainer());
        $this->assertSame('web_hemi_test_fixtures_test_web_form', $testForm->getName());
        $testForm->setName('x-form');
        $this->assertSame('x-form', $testForm->getName());

        $elementContainer = $this->invokePrivateMethod($testForm, 'getFormElementContainer');
        $this->assertInstanceOf(Web\FormElementContainer::class, $elementContainer);

        $testForm = new TestWebForm(new Web\FormElementContainer(), 'test_form');

        $this->assertTrue($testForm->isInitCalled);
        $this->assertSame('test_form', $testForm->getName());

        $this->assertInstanceOf(NestedElementInterface::class, $testForm->form);

        $attributes = $testForm->form->getAttributes();

        $this->assertSame('POST', $attributes['method']);
        $this->assertEmpty($attributes['action']);
    }

    /**
     * Tests simple validator. By default everything is valid.
     */
    public function testValidator()
    {
        $testForm = new TestWebForm(new Web\FormElementContainer(), 'test_form');

        $this->assertTrue($testForm->isValid());
    }

    /**
     * Tests salt setter.
     */
    public function testSalt()
    {
        $salt = 'test';
        $testForm = new TestWebForm(new Web\FormElementContainer(), 'test_form');

        $testForm->setNameSalt($salt);
        $this->assertSame(md5($salt), $testForm->salt);

        $testForm->setAutoComplete(false);
        $testForm->setName('y-form');
        $this->assertSame('y-form_'.md5($salt), $testForm->getName());
    }

    /**
     * Tests attribute setter.
     */
    public function testAttributes()
    {
        $testForm = new TestWebForm(new Web\FormElementContainer(), 'test_form');

        $attributes = $testForm->form->getAttributes();
        $this->assertSame('POST', $attributes['method']);
        $this->assertEmpty($attributes['action']);

        $testForm->setAction('some-url');
        $attributes = $testForm->form->getAttributes();
        $this->assertSame('POST', $attributes['method']);
        $this->assertSame('some-url', $attributes['action']);

        $testForm->setMethod('GET');
        $attributes = $testForm->form->getAttributes();
        $this->assertSame('GET', $attributes['method']);
        $this->assertSame('some-url', $attributes['action']);

        $testForm->doSetEnctype();
        $attributes = $testForm->form->getAttributes();
        $this->assertSame('GET', $attributes['method']);
        $this->assertSame('some-url', $attributes['action']);
        $this->assertSame('application/x-www-form-urlencoded', $attributes['enctype']);

        $testForm->doSetEnctype('multipart/form-data');
        $attributes = $testForm->form->getAttributes();
        $this->assertSame('GET', $attributes['method']);
        $this->assertSame('some-url', $attributes['action']);
        $this->assertSame('multipart/form-data', $attributes['enctype']);
    }

    /**
     * Tests auto-complete switcher.
     */
    public function testAutoCompete()
    {
        $salt = 'test';
        $testForm = new TestWebForm(new Web\FormElementContainer(), 'test_form');

        $testForm->setNameSalt($salt);
        $this->assertSame('test_form', $testForm->getName());

        $testForm->setAutoComplete(false);
        $this->assertSame('test_form_'.md5($salt), $testForm->getName());

        $testForm->setAutoComplete(true);
        $this->assertSame('test_form', $testForm->getName());

        $testForm->setNameSalt($salt.'x');
        $testForm->setAutoComplete(false);
        $this->assertSame('test_form_'.md5($salt.'x'), $testForm->getName());

        $testForm->setAutoComplete(true);
        $this->assertSame('test_form', $testForm->getName());
    }

    /**
     * Tests node setter/getter.
     */
    public function testNodes()
    {
        $testForm = new TestWebForm(new Web\FormElementContainer(), 'test_form');
        $nodes = [
            new Web\HiddenElement('hidden'),
            new Web\SelectElement('select')
        ];

        $instance = $testForm->doSetNodes($nodes);

        // objects are the same (not cloned)
        $this->assertInstanceOf(TestWebForm::class, $instance);
        $this->assertTrue($testForm === $instance);

        $actualNodes = $testForm->doGetNodes();

        // we get the same amount of nodes and the instances are the same
        $this->assertSame(count($nodes), count($actualNodes));
        $this->assertTrue($nodes[0] === $actualNodes['hidden']);
        $this->assertTrue($nodes[1] === $actualNodes['select']);
    }

    /**
     * Test data setter/getter
     */
    public function testData()
    {
        $salt = 'test';
        $testForm = new TestWebForm(new Web\FormElementContainer(), 'test_form');
        $inspectedData = [
            'info' => [
                'hidden' => 'test',
                'country' => ['de']
            ],
            'submit' => null
        ];
        // test get default data
        $this->assertArraysAreSimilar($inspectedData, $testForm->getData());
        $inspectedData = [
            'info' => [
                'hidden' => 'test 2',
                'country' => ['de', 'at']
            ],
            'submit' => null
        ];
        // test set and get data
        $testForm->setData($inspectedData);
        $this->assertArraysAreSimilar($inspectedData, $testForm->getData());

        $inspectedData = [
            'info' => [
                'hidden' => 'test 3',
                'country' => ['hu']
            ],
            'submit' => null
        ];
        // test setter with form name index
        $testForm->setData(['test_form' => $inspectedData]);
        $this->assertArraysAreSimilar($inspectedData, $testForm->getData());


        $inspectedData = [
            'info' => [
                'hidden' => 'test 4',
                'country' => ['hu', 'de', 'at']
            ],
            'submit' => null
        ];
        // test setter with form salted name index
        $testForm->setNameSalt($salt)
            ->setAutoComplete(false)
            ->setData(['test_form_'.md5($salt) => $inspectedData]);
        $this->assertArraysAreSimilar($inspectedData, $testForm->getData());

        $inspectedData = [
            'info' => [
                'hidden' => 'test 5',
                'country' => ['at']
            ],
            'submit' => null
        ];
        // test setter with form salted name index when no salt is used
        $testForm->setAutoComplete(false)
            ->setData(['test_form_'.md5($salt) => $inspectedData]);
        $this->assertArraysAreSimilar($inspectedData, $testForm->getData());
    }
}
