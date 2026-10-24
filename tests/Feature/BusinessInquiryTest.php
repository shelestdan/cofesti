<?php

namespace Tests\Feature;

use Tests\TestCase;

class BusinessInquiryTest extends TestCase
{
    public function test_home_business_scenario_links_to_partner_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertSee('class="hero-scenario hero-takeaway" href="'.route('business.index').'"', false)
            ->assertSee('Для<br>бизнеса', false)
            ->assertDontSee('Взять<br>с собой', false);
    }

    public function test_partner_page_contains_questionnaire_and_original_site_contacts(): void
    {
        $response = $this->get(route('business.index'));

        $response->assertSeeText('Анкета партнёра')
            ->assertSee('action="'.route('inquiries.store').'"', false)
            ->assertSee('tel:+79033312189', false)
            ->assertSee('tel:+79277778678', false)
            ->assertSee('mailto:kofesti.armat@yandex.ru', false)
            ->assertSeeText('Тимофеевка');
    }

    public function test_partner_questionnaire_saves_inquiry_and_returns_to_business_page(): void
    {
        $response = $this->from(route('business.index'))->post(route('inquiries.store'), [
            'type' => 'wholesale',
            'name' => 'Тестовый партнёр',
            'email' => 'partner@example.test',
            'message' => 'Кофейня в Самаре. Интересуют кофе и чай.',
            'privacy_accepted' => '1',
        ]);

        $response->assertRedirect(route('business.index'))->assertSessionHasNoErrors()->assertSessionHas('status');
        $this->assertDatabaseHas('inquiries', [
            'type' => 'wholesale',
            'name' => 'Тестовый партнёр',
            'email' => 'partner@example.test',
            'message' => 'Кофейня в Самаре. Интересуют кофе и чай.',
        ]);
    }

    public function test_partner_cannot_submit_questionnaire_without_contact_details(): void
    {
        $response = $this->from(route('business.index'))->post(route('inquiries.store'), [
            'type' => 'wholesale',
            'name' => 'Тестовый партнёр',
            'privacy_accepted' => '1',
        ]);

        $response->assertSessionHasErrors(['phone', 'email'])->assertRedirect(route('business.index'));
        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_partner_cannot_submit_questionnaire_without_consent(): void
    {
        $response = $this->from(route('business.index'))->post(route('inquiries.store'), [
            'type' => 'wholesale',
            'name' => 'Тестовый партнёр',
            'email' => 'partner@example.test',
        ]);

        $response->assertSessionHasErrors(['privacy_accepted']);
        $this->assertDatabaseCount('inquiries', 0);
    }
}
