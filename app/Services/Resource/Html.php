<?php

namespace App\Services\Resource;

use DOMNode;
use DOMXPath;
use DOMDocument;
use Illuminate\Support\Facades\Log;

class Html
{
    private DOMDocument $dom;
    private DOMNode $comment;

    public function __construct(
        string $html
    ) {
        $this->dom = $this->getDOMDocument($html);
    }

    private function getDOMDocument(
        string $html
    ): mixed {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        return $dom;
    }

    /**
     * отримуємо усі коментарі зі сторінки
     */
    public function getComments(): array
    {
        $datas = [];
        $xp = new DOMXPath($this->dom);
        $comments = $xp->query('//*[contains(@class, "styles_cardWrapper__")]');

        foreach ($comments as $comment) {
            $c = $this->getComment($comment);
            Log::info('Comment', compact('c'));
            $datas[] = $c;
        }

        return $datas;
    }

    /**
     * Збираємо коментар
     */
    private function getComment(
        DOMNode $comment
    ): object {
        $this->comment = $comment;

        $data = [
            'user' => $this->getCommentUser(),
            'rate' => $this->getCommentRate(),
            'date_added' => $this->getCommentDate(),
            'date_exp' => $this->getCommentDateExp(),
            'title' => $this->getCommentTitle(),
            'text' => $this->getCommentText()
        ];

        return json_decode(json_encode($data));
    }

    private function getDomListBlock(
        string $query
    ): DOMNode|false {
        $xp = new DOMXPath($this->dom);

        $block = $xp->query($query, $this->comment);
        return ($block !== false && $block->length) ? $block->item(0) : false;
    }

    /**
     * отримуємо користувача з коменту
     */
    private function getCommentUser(): array|false
    {
        $user = collect();

        $xp = new DOMXPath($this->dom);

        $aside = $this->getDomListBlock('.//aside[contains(@class, "styles_consumerInfoWrapper__")]');
        if (!$aside) {
            return false;
        }

        // Імя користувача
        $n = $xp->query('.//span[@data-consumer-name-typography="true"]', $aside);
        if ($n->length) {
            $user->put('name', trim($n->item(0)->textContent));
        }

        // ІД користувача
        $n = $xp->query('.//a[@data-consumer-profile-link="true"]', $aside);
        if ($n->length) {
            $user->put('resource_user_id', str_replace('/users/', '', $n->item(0)->getAttribute('href')));
        }

        // Код країни
        $n = $xp->query('.//span[@data-consumer-country-typography="true"]', $aside);
        if ($n->length) {
            $user->put('country_code', trim($n->item(0)->textContent));
        }

        // К-сть коментарів
        $n = $xp->query('.//*[@data-consumer-reviews-count]', $aside);
        if ($n->length) {
            $user->put('comments_count', (int)$n->item(0)->getAttribute('data-consumer-reviews-count'));
        }

        // Аватарка
        $n = $xp->query('.//img[@data-consumer-avatar-image="true"]', $aside);
        if ($n->length) {
            $user->put('img', $n->item(0)->getAttribute('src'));
        }

        return $user->toArray();
    }

    private function getCommentRate(): int|string
    {
        $xp = new DOMXPath($this->dom);

        $n = $xp->query('.//*[@data-service-review-rating]', $this->comment);
        return $n->length ? (int)$n->item(0)->getAttribute('data-service-review-rating') : 0;
    }

    private function getCommentDate(): string
    {
        $xp = new DOMXPath($this->dom);

        $n = $xp->query('.//time[@data-service-review-date-time-ago="true"]', $this->comment);
        return $n->length ? $n->item(0)->getAttribute('datetime') : '';
    }

    private function getCommentDateExp(): string
    {
        $xp = new DOMXPath($this->dom);

        $section = $this->getDomListBlock('.//section[contains(@class, "styles_reviewContentwrapper__")]');
        if (!$section) {
            return '';
        }

        $n = $xp->query('.//*[contains(@class, "CDS_Typography_appearance-subtle__")]', $section);
        return $n->length ? trim($n->item(0)->textContent) : '';
    }

    private function getCommentTitle(): string
    {
        $xp = new DOMXPath($this->dom);

        $n = $xp->query('.//h2[@data-service-review-title-typography="true"]', $this->comment);
        return $n->length ? trim($n->item(0)->textContent) : '';
    }

    private function getCommentText(): string
    {
        $xp = new DOMXPath($this->dom);

        $n = $xp->query('.//p[@data-service-review-text-typography="true"]', $this->comment);
        return $n->length ? trim($n->item(0)->textContent) : '';
    }

    public function getNextPage(): string|null
    {
        $xp = new DOMXPath($this->dom);

        $n = $xp->query('.//a[@name="pagination-button-next"]')->item(0);
        return $n->hasAttribute('href') ? $n->getAttribute('href') : null;
    }
}
