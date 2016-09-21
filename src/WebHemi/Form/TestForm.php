<?php
/**
 * WebHemi.
 *
 * PHP version 5.6
 *
 * @copyright 2012 - 2016 Gixx-web (http://www.gixx-web.com)
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link      http://www.gixx-web.com
 */
namespace WebHemi\Form;

use WebHemi\Form\Element\Web;

/**
 * Class TestForm
 *
 * @codeCoverageIgnore - only for test purposes
 */
class TestForm extends AbstractForm
{
    /** @var  Web\FormElement */
    protected $form;

    /**
     * Returns the form container element. E.g.: for HTML forms it is the <form> tag.
     *
     * @return Web\FormElement
     */
    protected function getFormContainer()
    {
        return new Web\FormElement();
    }

    /**
     * Initialize form.
     *
     * @return void
     */
    protected function initForm()
    {
        // Hidden field.
        $hiddenCSRF = new Web\HiddenElement('csrf');
        $hiddenCSRF->setValue('Some CSRF test value');

        $xContent = <<<EOH
<h2>This is a static content</h2>
<p>
    Veritus invenire mei ne. No nam nullam probatus, mea te nostro delicatissimi. Ea deserunt pericula cum, no eos 
    tation rationibus scriptorem. Mel ut verterem invenire. Eu vim aperiam nonumes sententiae, ex quas sapientem has. 
    Mea autem adolescens ea, ea ridens oportere sit, ex dicta tacimates nec. Scaevola complectitur ad sed, ne ius 
    praesent argumentum.
</p>
<p>
    Pro no dicam aliquid bonorum. Inimicus imperdiet mei et. Ut hendrerit adversarium sed. Te cum everti delicata, 
    quis utamur argumentum pri id, oratio exerci everti eam te.
</p>
<p>
    No meliore deterruisset nec. An eros electram constituto duo, has reque corrumpit at. Nihil aperiri liberavisse in 
    has, eu qui nonumy nusquam. Mazim salutatus te nam, nobis putent mentitum mea ne. Mundi denique at ius, 
    illum quaestio vix id.
</p>
EOH;
        $fieldSetX = new Web\StaticContentElement('notice', 'Please Note');
        $fieldSetX->setValue($xContent);

        // Field sets
        $fieldSet1 = $this->getMainFieldSet();
        $fieldSet2 = $this->getLocationFieldSet();

        // Submit button.
        $submit = new Web\SubmitElement('submit', 'Login');

        // Assign elements and the field sets to the form
        $this->setNodes(
            [
                $hiddenCSRF,
                $fieldSet1,
                $fieldSetX,
                $fieldSet2,
                $submit
            ]
        );
    }

    /**
     * Gets main field set
     *
     * @return Web\FieldSetElement
     */
    private function getMainFieldSet()
    {
        // Field set
        $fieldSet = new Web\FieldSetElement('info', 'Login form');

        // Text input with custom attribute
        $loginname = new Web\TextElement('username', 'Username');
        $loginname->setAttributes(['placeholder' => 'Your login name']);

        // Password input
        $password = new Web\PasswordElement('password', 'Password');
        $password->setErrors(
            [
                'It just doesn\'t work, buddy...'
            ]
        );

        // HTML5 email input
        $email = new Web\InputElement('email', 'Email');
        $email->setType('email');

        // Single-option checkbox.
        // For single-option radio boxes the ->setValue(1) is equals to this: ->setAttribute('checked', true);
        $checkboxRememberMe = new Web\CheckboxElement('remember_me', 'Remember Me');
        $checkboxRememberMe->setValue('1');

        // Radio group.
        $radioLanguage = new Web\RadioElement('language', 'Select language');
        $radioLanguage->setOptions(
            [
                ['label' => 'English', 'value' => 'en', 'checked' => true],
                ['label' => 'German', 'value' => 'de', 'checked' => true],
                ['label' => 'Spanish', 'value' => 'es', 'checked' => true],
            ]
        );

        // Checkbox group.
        $checkboxTerms = new Web\CheckboxElement('terms', 'Terms and conditions');
        $checkboxTerms->setOptions(
            [
                ['label' => 'I am human.', 'value' => 'human'],
                ['label' => 'I have read the T&C.', 'value' => 'terms_read', 'checked' => false],
                ['label' => 'I want newsletters.', 'value' => 'terms_newsletter', 'checked' => true],
            ]
        );

        // Assign elements to the field set
        $fieldSet->setNodes(
            [
                $loginname,
                $password,
                $email,
                $checkboxRememberMe,
                $radioLanguage,
                $checkboxTerms
            ]
        );

        return $fieldSet;
    }

    /**
     * Gets location field set.
     *
     * @return Web\FieldSetElement
     */
    private function getLocationFieldSet()
    {
        // Field set
        $fieldSet = new Web\FieldSetElement('location', 'Location');

        // Select box with no multi selection and with no option groups
        $select1 = new Web\SelectElement('country', 'I live in:');
        $select1->setOptions(
            [
                ['label' => 'Hungary', 'value' => 'hu'],
                ['label' => 'Germany', 'value' => 'de', 'checked' => true],
                ['label' => 'Austria', 'value' => 'at'],
            ]
        );

        // Select box with no multi selection and WITH option groups
        $select2 = new Web\SelectElement('dream', 'I\'d like live in:');
        $select2->setOptions(
            [
                ['label' => 'Anywhere', 'value' => '*'],
                ['label' => 'Hungary', 'value' => 'hu', 'group' => 'Europe'],
                ['label' => 'USA', 'value' => 'us', 'group' => 'America'],
                ['label' => 'Germany', 'value' => 'de', 'group' => 'Europe'],
                ['label' => 'Austria', 'value' => 'at', 'group' => 'Europe'],
                ['label' => 'Canada', 'value' => 'ca', 'group' => 'America'],
                ['label' => 'Switzerland', 'value' => 'ch', 'group' => 'Europe', 'checked' => true],
                ['label' => 'France', 'value' => 'fr', 'group' => 'Europe'],
                ['label' => 'Spain', 'value' => 'es', 'group' => 'Europe'],
                ['label' => 'Japan', 'value' => 'jp', 'group' => 'Asia'],
            ]
        )
            ->setErrors(
                [
                    'This has some errors.',
                    'And also has a major problem.'
                ]
            );

        // Select box WITH multi selection.
        $select3 = new Web\SelectElement('past', 'I\'ve already lived in:');
        $select3->setOptions(
            [
                ['label' => 'Hungary', 'value' => 'hu', 'group' => 'Europe', 'checked' => true],
                ['label' => 'USA', 'value' => 'us', 'group' => 'America'],
                ['label' => 'Germany', 'value' => 'de', 'group' => 'Europe', 'checked' => true],
                ['label' => 'Austria', 'value' => 'at', 'group' => 'Europe'],
                ['label' => 'Canada', 'value' => 'ca', 'group' => 'America'],
                ['label' => 'Switzerland', 'value' => 'ch', 'group' => 'Europe'],
                ['label' => 'France', 'value' => 'fr', 'group' => 'Europe'],
                ['label' => 'Spain', 'value' => 'es', 'group' => 'Europe'],
                ['label' => 'Japan', 'value' => 'jp', 'group' => 'Asia'],
            ]
        )
            ->setAttributes(
                [
                    'multiple' => true,
                    'size' => 10
                ]
            );

        // Assign elements to the field set
        $fieldSet->setNodes(
            [
                $select1,
                $select2,
                $select3
            ]
        );

        return $fieldSet;
    }
}