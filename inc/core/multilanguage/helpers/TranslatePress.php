<?php

namespace Waboot\inc\core\multilanguage\translatepress;

class TranslatePress
{
    /**
     * @return \TRP_Query
     * @throws \RuntimeException
     */
    public static function getTRPQuery(): \TRP_Query {
        static $trpQuery;
        if (isset($trpQuery)) {
            return $trpQuery;
        }
        $trp = \TRP_Translate_Press::get_trp_instance();
        $trpQuery = $trp->get_component('query');
        if (!$trpQuery instanceof \TRP_Query) {
            throw new \RuntimeException('Impossibile ottenere una istanza di TRP_Query');
        }
        return $trpQuery;
    }

    /**
     * @return \wpdb
     * @throws \RuntimeException
     */
    public static function getWPDbFromTRPQuery(): \wpdb {
        $trpQuery = self::getTRPQuery();
        return $trpQuery->db;
    }

    /**
     * @param $string
     * @return false|int
     * @throws \RuntimeException
     */
    public static function originalStringExists($string) {
        $q = 'SELECT id FROM `' . getWPDbFromTRPQuery()->prefix . 'trp_original_strings` WHERE original = %s';
        $qPrepared = self::getWPDbFromTRPQuery()->prepare($q, $string);
        $searching = self::getWPDbFromTRPQuery()->get_results($qPrepared);
        if (!\is_array($searching) || count($searching) === 0) {
            return false;
        }
        return (int)$searching[0]->id;
    }

    /**
     * @param $string
     * @return string
     */
    public static function sanitizeStringForDB($string): string {
        $stringWithSpaces = htmlentities($string);
        if (preg_match('/&nbsp;/', $stringWithSpaces)) {
            $sanitizedString = preg_replace('/(&nbsp;)/', '', $stringWithSpaces);
            $string = html_entity_decode($sanitizedString);
        }
        if (function_exists('\trp_sanitize_string')) {
            $string = \trp_sanitize_string($string);
        }
        $string = wptexturize($string);
        $string = trim($string);
        return $string;
    }

    /**
     * @param string $originalText
     * @param string $locale
     * @return array
     * @throws \RuntimeException $e
     */
    public static function saveOriginalString(string $originalText, string $locale): array {
        $dictionaryTable = sanitize_text_field(self::getTRPQuery()->get_table_name($locale));
        self::getTRPQuery()->insert_strings([$originalText], $locale);
        $dictionaryEntryId = self::getTRPQuery()->db->insert_id;
        $searchingForOriginalId = self::getTRPQuery()->db->get_results(self::getTRPQuery()->db->prepare('SELECT original_id FROM `' . $dictionaryTable . '` WHERE id = %d', $dictionaryEntryId));
        if (!\is_array($searchingForOriginalId) || count($searchingForOriginalId) === 0) {
            throw new \RuntimeException('Inserimento stringa fallito');
        }
        return [
            'entry_id' => $dictionaryEntryId,
            'original_id' => $searchingForOriginalId[0]->original_id
        ];
    }

    /**
     * @param $id
     * @param $dictionaryTable
     * @return array|null
     */
    public static function getDictionaryEntryForOriginalId($id, $dictionaryTable): ?array {
        $results = self::getDictionaryEntriesForOriginalId($id, $dictionaryTable);
        if (!\is_array($results) || count($results) === 0) {
            return null;
        }
        return $results[0];
    }

    /**
     * @param $id
     * @param $dictionaryTable
     * @return bool
     */
    public static function hasMultipleEntries($id, $dictionaryTable): bool {
        $results = self::getDictionaryEntriesForOriginalId($id, $dictionaryTable);
        return \is_array($results) && count($results) > 1;
    }

    /**
     * @param $id
     * @param $dictionaryTable
     * @return array
     */
    public static function getDictionaryEntriesForOriginalId($id, $dictionaryTable): array {
        $q = 'SELECT * FROM `' . $dictionaryTable . '` WHERE original_id = %d';
        $qPrepared = getWPDbFromTRPQuery()->prepare($q, $id);
        $results = getWPDbFromTRPQuery()->get_results($qPrepared, ARRAY_A);
        if (!\is_array($results) || count($results) === 0) {
            return [];
        }
        return $results;
    }
}