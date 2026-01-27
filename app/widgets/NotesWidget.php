<?php

declare(strict_types=1);

/**
 * Notes Widget - Displays user-defined text content
 *
 * Simple sticky note for reminders and quick references
 */
class NotesWidget extends Widget
{
    /**
     * Fetch widget data
     *
     * @return array Widget data containing note content
     */
    public function getData(): array
    {
        $content = $this->getSetting('content', '');

        return [
            'content' => $content
        ];
    }

    /**
     * Validate widget settings
     *
     * @param array $settings Settings to validate
     * @return array Validation result
     */
    public function validateSettings(array $settings): array
    {
        // Reason: No validation needed, any text content is valid
        return [
            'valid' => true,
            'errors' => []
        ];
    }

    /**
     * Get default settings
     *
     * @return array Default settings
     */
    public function getDefaultSettings(): array
    {
        return [
            'content' => 'Enter your notes here...'
        ];
    }
}
