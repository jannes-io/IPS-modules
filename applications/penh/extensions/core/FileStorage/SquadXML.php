<?php
/**
 * @brief        File Storage Extension: SquadXML
 * @author        <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license        https://www.invisioncommunity.com/legal/standards/
 * @package        Invision Community
 * @subpackage    PERSCOM Enhanced
 * @since        01 Feb 2022
 */

namespace IPS\penh\extensions\core\FileStorage;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * File Storage Extension: SquadXML
 */
class _SquadXML
{
    /**
     * Count stored files
     *
     * @return    int
     */
    public function count()
    {
        return \IPS\Db::i()->select('COUNT(*)', '...', '...')->first();
    }

    /**
     * Move stored files
     *
     * @param int $offset This will be sent starting with 0, increasing to get all files stored by this extension
     * @param int $storageConfiguration New storage configuration ID
     * @param int|NULL $oldConfiguration Old storage configuration ID
     * @return    void|int                            An offset integer to use on the next cycle, or nothing
     * @throws    \UnderflowException                    When file record doesn't exist. Indicating there are no more files to move
     */
    public function move($offset, $storageConfiguration, $oldConfiguration = null)
    {
        \IPS\File::get($oldConfiguration ?: 'penh_SquadXML', '...')->move($storageConfiguration);
    }

    /**
     * Check if a file is valid
     *
     * @param string $file The file path to check
     * @return    bool
     */
    public function isValidFile($file): bool
    {
        return true;
    }

    /**
     * Delete all stored files
     *
     * @return    void
     */
    public function delete()
    {
    }
}
