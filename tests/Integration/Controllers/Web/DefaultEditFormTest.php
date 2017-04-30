<?php
namespace Czim\CmsModels\Test\Integration\Controllers\Web;

use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Integration\Controllers\AbstractControllerIntegrationTest;

/**
 * Class DefaultEditFormTest
 *
 * Tests for simple model edit form. Note that browser/javascript functionality is deliberately not tested here.
 *
 * @group integration
 * @group controllers
 */
class DefaultEditFormTest extends AbstractControllerIntegrationTest
{
    const ROUTE_BASE = 'cms::models.model.czim-cmsmodels-test-helpers-models-testpost';


    /**
     * @test
     */
    function it_shows_an_edit_model_form()
    {
        $this->visitRoute(static::ROUTE_BASE . '.edit', [1])->seeStatusCode(200);

        static::assertHtmlElementInResponse('form.model-form');
        $form = $this->crawler()->filter('form.model-form');
        static::assertEquals(1, $form->attr('data-id'), 'Form data-id is incorrect');
        static::assertEquals(TestPost::class, $form->attr('data-class'), 'Form data-class is incorrect');
        static::assertEquals('post', strtolower($form->attr('method')), 'Incorrect form method');
        static::assertEquals('multipart/form-data', strtolower($form->attr('enctype')), 'Incorrect form enctype');
        static::assertEquals(
            'http://localhost/cms/model/czim-cmsmodels-test-helpers-models-testpost/1',
            $form->attr('action'),
            'Incorrect form action'
        );

        static::assertHtmlElementInResponse(
            'form.model-form input[name=_method][value=put]',
            '_method input with put not present'
        );

        static::assertHtmlElementInResponse('input[name="description"]', 'Missing input field');
        static::assertHtmlElementInResponse('select[name="type"]', 'Missing input field');
        static::assertHtmlElementInResponse('input[name="checked"]', 'Missing input field');
        static::assertHtmlElementInResponse('input[name="title[en]"]', 'Missing input field');
        static::assertHtmlElementInResponse('input[name="title[nl]"]', 'Missing input field');
        static::assertHtmlElementInResponse('textarea[name="body[en]"]', 'Missing input field');
        static::assertHtmlElementInResponse('textarea[name="body[nl]"]', 'Missing input field');
        static::assertHtmlElementInResponse('select[name="author"]', 'Missing input field');
        static::assertHtmlElementInResponse('select[name="comments[]"]', 'Missing input field');

        static::assertHtmlElementInResponse('.translated-form-field-locale-select', 'Local switch missing');
        static::assertHtmlElementInResponse('li.translated-form-field-locale-option a[data-locale=en]');
        static::assertHtmlElementInResponse('li.translated-form-field-locale-option a[data-locale=nl]');
    }

    /**
     * @test
     * @depends it_shows_an_edit_model_form
     */
    function it_updates_a_model_on_form_submit()
    {
        $this->visitRoute(static::ROUTE_BASE . '.edit', [1])->seeStatusCode(200);

        $this->makeRequestUsingForm(
            $this->crawler()->filter('form.model-form')->first()->form()
                ->setValues([
                    'description' => 'Updated description',
                    'title'       => ['en' => 'New English Title'],
                ])
        );
        $this->seeStatusCode(200);

        // The form should now be in edit mode
        static::assertHtmlElementInResponse('form.model-form');
        $form = $this->crawler()->filter('form.model-form');
        static::assertEquals(1, $form->attr('data-id'), 'Form data-id should be 1');

        static::assertHtmlElementInResponse(
            'form.model-form input[name=_method][value=put]',
            '_method input with put not present'
        );

        // Check if values were updated
        static::assertHtmlElementInResponse(
            'input[name="description"][value="Updated description"]',
            'Description not found or not updated'
        );
        static::assertHtmlElementInResponse(
            'input[name="title[en]"][value="New English Title"]',
            'English title not found or not updated'
        );

        static::assertHtmlElementInResponse('div.alert.alert-success', 'There should be a success message');
    }

    /**
     * @test
     * @depends it_shows_an_edit_model_form
     */
    function it_updates_a_model_and_returns_to_listing_using_save_and_close_mode()
    {
        $this->visitRoute(static::ROUTE_BASE . '.edit', [1])->seeStatusCode(200);

        $this->makeRequestUsingForm(
            $this->crawler()->filter('form.model-form')->first()->form()
                ->setValues([
                    '__save_and_close__' => 1,
                    'description'        => 'Updated description',
                ])
        );
        $this->seeStatusCode(200);

        // The response should be a listing
        static::assertNotHtmlElementInResponse('form.model-form');
        static::assertHtmlElementInResponse('div.alert.alert-success', 'There should be a success message');
    }

    /**
     * @test
     * @depends it_shows_an_edit_model_form
     */
    function it_shows_validation_errors_for_invalid_input()
    {
        $this->visitRoute(static::ROUTE_BASE . '.edit', [1])->seeStatusCode(200);

        $this->makeRequestUsingForm(
            $this->crawler()->filter('form.model-form')->first()->form()
                ->disableValidation()
                ->setValues([
                    'type' => 'incorrect',
                ])
        );
        $this->seeStatusCode(200);

        // The form should still be in create mode
        static::assertHtmlElementInResponse('form.model-form');
        static::assertHtmlElementInResponse('div.alert.alert-danger', 'There should be a general alert message');
        static::assertHtmlElementInResponse('#type-errors.help-block', 'There should be an error message for type input');
    }

}
