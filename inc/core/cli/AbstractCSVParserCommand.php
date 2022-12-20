<?php

namespace Waboot\inc\core\cli;

use League\Csv\Reader;

abstract class AbstractCSVParserCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'csv-parser';
    /**
     * @var string
     */
    protected $logFileName = 'csv-parser';
    protected string $importDirPath;
    protected string $sourceFilePath;
    protected string $delimiter;
    protected string $parseAllFiles;
    /**
     * @var CSVRow
     */
    protected $currentCSVRow;

    public static function getCommandDescription(): array
    {
        $description = parent::getCommandDescription();
        $description['shortdesc'] = 'Parse a CSV file';
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'basepath',
            'description' => 'Specifies the base path from which reads CSV files',
            'default' => WP_CONTENT_DIR . '/imports/csv',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'file',
            'description' => 'Specifies the CSV file path to import (relative to base path). If not specified the script will try to find a file named like: '.'csv-'.(new \DateTime('now',new \DateTimeZone('Europe/Rome')))->format('ymd').'.csv',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'delimiter',
            'description' => 'Specify the CSV delimiter',
            'default' => ',',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'parse-all-files',
            'description' => 'Specifies whether parse all files inside the base path',
            'optional' => true,
        ];
        return $description;
    }

    public function __invoke($args, $assoc_args): int
    {
        parent::__invoke($args, $assoc_args);
        try {
            $importDirPath = $assoc_args['basepath'] ?? $this->getDefaultImportDirPath();
            if(!\is_dir($importDirPath)){
                $importDirCreated = wp_mkdir_p($importDirPath);
                if(!$importDirCreated){
                    throw new \RuntimeException('Impossibile creare la directory: '.$importDirPath);
                }
            }
            $this->importDirPath = $importDirPath;
            $this->parseAllFiles = isset($assoc_args['parse-all-files']);
            if($this->parseAllFiles){
                $this->log('Parsing di tutti i files');
            }
            $sourceFilePath = false;
            if(isset($assoc_args['file'])){
                $sourceFilePath = $assoc_args['file'];
            }elseif(!$this->parseAllFiles){
                $sourceFilePath = $this->getDefaultSourceFilePath();
            }
            if($sourceFilePath !== false && !\is_file($sourceFilePath)){
                $sourceFilePath = $importDirPath.'/'.$sourceFilePath;
                if(!\is_file($sourceFilePath)){
                    $this->error('Impossibile trovare il file: '.$sourceFilePath);
                }
            }
            $this->delimiter = $assoc_args['delimiter'] ?? $this->getDefaultCSVDelimiter();
            $this->customInitialization($args,$assoc_args);
            if($this->parseAllFiles){
                foreach ($this->fetchFiles() as $sourceFilePath){
                    $this->sourceFilePath = $sourceFilePath;
                    $this->log('Selezionato il file: '.$sourceFilePath);
                    $this->parseCSV();
                    $this->onDoneParsing();
                    try{
                        if(!$this->isDryRun()){
                            $this->setLocalFileAsParsed();
                        }
                        $this->log('File locale settato come parsato');
                    }catch (\Exception $e){
                        $this->error('ERRORE: Impossibile settare il file locale come parsato');
                        return 1;
                    }
                }
            }else{
                $this->sourceFilePath = $sourceFilePath;
                $this->log('Selezionato il file: '.$sourceFilePath);
                $this->parseCSV();
                $this->onDoneParsing();
                try{
                    if(!$this->isDryRun()){
                        $this->setLocalFileAsParsed();
                    }
                    $this->log('File locale settato come parsato');
                }catch (\Exception $e){
                    $this->error('ERRORE: Impossibile settare il file locale come parsato');
                    return 1;
                }
            }
            $this->onBeforeCommandEnd();
            $this->success('Done');
            return 0;
        }catch (\Exception | \Throwable $e){
            $this->log('ERRORE: '.$e->getMessage());
            return 1;
        }
    }

    abstract function customInitialization($args,$assoc_args): void;

    protected function onBeforeCommandEnd(): void {}

    protected function onDoneParsing(): void {}

    /**
     * @return void
     * @throws \League\Csv\Exception
     * @throws \WC_Data_Exception
     */
    protected function parseCSV(): void
    {
        $csv = Reader::createFromPath($this->sourceFilePath);
        $this->log('Parsing del file...');
        $csv->setDelimiter($this->delimiter);
        $csv->setHeaderOffset(0);
        $this->log('Counting dei record...');
        foreach ($csv->getRecords() as $r) {
            try {
                $this->currentCSVRow = $this->createCSVColumnInstance($r);
                $this->parseCSVRow();
            }catch (\Exception | \Throwable $e){
                $this->log('ERRORE: '.$e->getMessage());
                //var_dump($e);
                continue;
            }
        }
    }

    abstract protected function parseCSVRow(): void;

    /**
     * @return array|false
     */
    protected function fetchFiles(): array
    {
        $files = glob($this->importDirPath.'/*.csv');
        if(!\is_array($files)){
            return [];
        }
        // sort files by last modified date
        usort($files, static function($x, $y) {
            $mtx = filemtime($x);
            $mty = filemtime($y);
            if($mtx === $mty){

            }
            return $mtx < $mty ? -1 : 1;
        });
        return $files;
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function setLocalFileAsParsed(): void
    {
        $parsedDestination = $this->importDirPath.'/parsed';
        if (!is_dir($parsedDestination)) {
            if(!mkdir($parsedDestination, 0777, true) && !is_dir($parsedDestination)){
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $parsedDestination));
            }
        }
        $parsedDestination .= '/' . basename($this->sourceFilePath);
        if(!rename($this->sourceFilePath,$parsedDestination)) {
            $this->log(sprintf('ERRORE: Failed to move file %s', $this->sourceFilePath));
        }
    }

    /**
     * @param array $rowData
     * @return CSVRow
     */
    protected function createCSVColumnInstance(array $rowData): CSVRow
    {
        return new CSVRow($rowData);
    }

    /**
     * @return string
     */
    protected function getDefaultImportDirPath(): string
    {
        return WP_CONTENT_DIR . '/imports/csv';
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getDefaultSourceFilePath(): string
    {
        return 'csv-'.(new \DateTime('now',new \DateTimeZone('Europe/Rome')))->format('ymd').'.csv';
    }

    /**
     * @return string
     */
    protected function getDefaultCSVDelimiter(): string
    {
        return ',';
    }
}
