<?php

declare(strict_types=1);

namespace App\Services\Cms;

class SystemSettingService
{
    public function information(): array
    {
        $stmt = db()->query('SELECT * FROM information WHERE id = 1 LIMIT 1');
        $row = $stmt->fetch();
        return is_array($row) ? $row : [];
    }

    public function update(array $payload): void
    {
        $stmt = db()->prepare(
            'UPDATE information
             SET title_website = :title_website,
                 url_default = :url_default,
                 meta_author = :meta_author,
                 footer = :footer,
                 whatsapp = :whatsapp,
                 facebook = :facebook,
                 twitter = :twitter,
                 instagram = :instagram,
                 linkedin = :linkedin,
                 youtube = :youtube,
                 email = :email,
                 phone = :phone,
                 address = :address,
                 base_color = :base_color,
                 second_color = :second_color,
                 gmaps = :gmaps,
                 embed_js = :embed_js,
                 meta_keyword = :meta_keyword,
                 meta_description = :meta_description,
                 meta_logo = :meta_logo,
                 meta_icon = :meta_icon,
                 meta_image = :meta_image,
                 footer_show_frontpage = :footer_show_frontpage,
                 footer_show_articles = :footer_show_articles,
                 footer_show_pages = :footer_show_pages,
                 footer_page_category_id = :footer_page_category_id,
                 footer_page_category_id_2 = :footer_page_category_id_2,
                 footer_page_category_id_3 = :footer_page_category_id_3,
                 footer_page_category_id_4 = :footer_page_category_id_4,
                 updated_at = :updated_at
             WHERE id = 1'
        );
        $stmt->execute([
            'title_website' => $this->nullableText($payload['title_website'] ?? null),
            'url_default' => $this->nullableText($payload['url_default'] ?? null),
            'meta_author' => $this->nullableText($payload['meta_author'] ?? null),
            'footer' => $this->nullableText($payload['footer'] ?? null),
            'whatsapp' => $this->nullableText($payload['whatsapp'] ?? null),
            'facebook' => $this->nullableText($payload['facebook'] ?? null),
            'twitter' => $this->nullableText($payload['twitter'] ?? null),
            'instagram' => $this->nullableText($payload['instagram'] ?? null),
            'linkedin' => $this->nullableText($payload['linkedin'] ?? null),
            'youtube' => $this->nullableText($payload['youtube'] ?? null),
            'email' => $this->nullableText($payload['email'] ?? null),
            'phone' => $this->nullableText($payload['phone'] ?? null),
            'address' => $this->nullableText($payload['address'] ?? null),
            'base_color' => $this->nullableText($payload['base_color'] ?? null),
            'second_color' => $this->nullableText($payload['second_color'] ?? null),
            // Keep as plain text from settings; sanitized by sanitize_gmaps_iframe_only() at render time.
            'gmaps' => $this->normalizeEmbeddedHtml($payload['gmaps'] ?? null),
            'embed_js' => $this->nullableText($payload['embed_js'] ?? null),
            'meta_keyword' => $this->nullableText($payload['meta_keyword'] ?? null),
            'meta_description' => $this->nullableText($payload['meta_description'] ?? null),
            'meta_logo' => $this->nullableText($payload['meta_logo'] ?? null),
            'meta_icon' => $this->nullableText($payload['meta_icon'] ?? null),
            'meta_image' => $this->nullableText($payload['meta_image'] ?? null),
            'footer_show_frontpage' => $this->normalizeBooleanFlag($payload['footer_show_frontpage'] ?? 1),
            'footer_show_articles' => $this->normalizeBooleanFlag($payload['footer_show_articles'] ?? 1),
            'footer_show_pages' => $this->normalizeBooleanFlag($payload['footer_show_pages'] ?? 1),
            'footer_page_category_id' => $this->normalizeNullableId($payload['footer_page_category_id'] ?? null),
            'footer_page_category_id_2' => $this->normalizeNullableId($payload['footer_page_category_id_2'] ?? null),
            'footer_page_category_id_3' => $this->normalizeNullableId($payload['footer_page_category_id_3'] ?? null),
            'footer_page_category_id_4' => $this->normalizeNullableId($payload['footer_page_category_id_4'] ?? null),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function footerPageCategories(): array
    {
        $stmt = db()->query('SELECT id, name_category FROM category ORDER BY urutan DESC, id DESC');
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text !== '' ? $text : null;
    }

    private function normalizeEmbeddedHtml(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return null;
        }

        $previous = null;
        while ($previous !== $text) {
            $previous = $text;
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return trim($text) !== '' ? trim($text) : null;
    }

    private function normalizeBooleanFlag(mixed $value): int
    {
        return (int) ((int) $value === 1);
    }

    private function normalizeNullableId(mixed $value): ?int
    {
        $id = (int) $value;
        return $id > 0 ? $id : null;
    }

}
