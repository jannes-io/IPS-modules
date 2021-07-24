<?php

namespace IPS\penh\extensions\core\FileStorage;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * File Storage Extension: DefaultUniform
 */
class _DefaultUniform
{
    public function count(): int
    {
        return empty(\IPS\Settings::i()->penh_personnel_default_uniform) ? 0 : 1;
    }

    public function move($offset, $storageConfiguration, $oldConfiguration = null): void
    {
        if ($this->count() > 0) {
            $file = \IPS\File::get($oldConfiguration ?: 'penh_DefaultUniform', \IPS\Settings::i()->penh_personnel_default_uniform)->move($storageConfiguration);

            if ((string)$file !== \IPS\Settings::i()->penh_personnel_default_uniform) {
                \IPS\Settings::i()->changeValues(['penh_personnel_default_uniform' => (string)$file]);
            }
        }
    }

    public function isValidFile($file): bool
    {
        return $this->count() > 0;
    }

    public function delete(): void
    {
        if ($this->count() > 0) {
            $file = \IPS\File::get('penh_DefaultUniform', \IPS\Settings::i()->penh_personnel_default_uniform);
            $file->delete();
        }
    }
}
