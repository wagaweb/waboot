<?php
namespace Waboot\inc\cli\product_import;

use Illuminate\Database\Schema\Blueprint;
use League\Csv\Exception;
use League\Csv\Reader;
use Waboot\inc\core\cli\AbstractCSVParserCommand;
use Waboot\inc\core\cli\CSVRow;
use Waboot\inc\core\DBException;
use Waboot\inc\core\facades\Query;
use function Waboot\inc\core\helpers\Waboot;

class BuildTermsReferenceTable extends AbstractCSVParserCommand
{
    private bool $mustRebuildTable = false;
    private string $tableName;

    public static function getCommandDescription(): array
    {
        $description = parent::getCommandDescription();
        $description['shortdesc'] = 'Build terms reference table from a well-formatted CSV';
        $description['longdesc'] = '## EXAMPLES' . "\n\n" . 'wp wawoo:build-term-reference-table --file=baz.csv';
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'table-name',
            'description' => 'Specifies the table name (default to: product_importer_terms_reference)',
            'default' => 'product_importer_terms_reference',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'rebuild-table',
            'description' => 'Specifies whether to drop and recreate the table',
            'optional' => true,
        ];
        return $description;
    }

    /**
     * @param $args
     * @param $assoc_args
     * @return void
     * @throws \Waboot\inc\core\DBException
     * @throws Exception
     * @throws BuildTermsReferenceTableException
     */
    function customInitialization($args, $assoc_args): void
    {
        $tableName = $assoc_args['table-name'];
        if(\is_string($tableName) && $tableName !== ''){
            $this->tableName = $tableName;
        }else{
            $this->tableName = 'product_importer_terms_reference';
        }
        $this->mustRebuildTable = isset($assoc_args['rebuild-table']);
        if($this->mustRebuildTable){
            $this->log('Dropping existing table...');
            Waboot()->DB()->getSchemaBuilder()->dropIfExists($this->tableName);
        }
        if(!Waboot()->DB()->tableExists($this->tableName)){
            $this->log('Creating table: '.$this->tableName);
            $csv = Reader::createFromPath($this->sourceFilePath);
            $csv->setDelimiter($this->delimiter);
            $csv->setHeaderOffset(0);
            $headers = $csv->getHeader();
            if(!\is_array($headers) || empty($headers)){
                throw new BuildTermsReferenceTableException('Invalid file headers');
            }
            $headers = array_map('strtolower',$headers);
            if(!\in_array('code',$headers)){
                throw new BuildTermsReferenceTableException('No "Code" header in the source file');
            }
            if(!\in_array('taxonomy',$headers)){
                throw new BuildTermsReferenceTableException('No "Taxonomy" header in the source file');
            }
            $this->log('- With headers: '.implode(',',$headers));
            Waboot()->DB()->getSchemaBuilder()->create($this->tableName, static function(Blueprint $blueprint) use($headers) {
                $blueprint->string('code')->unique()->primary();
                $blueprint->string('taxonomy');
                foreach ($headers as $header){
                    if(\in_array($header,['code','taxonomy'])){
                        continue;
                    }
                    $blueprint->string($header)->nullable();
                }
            });
            if(!Waboot()->DB()->tableExists($this->tableName)){
                throw new BuildTermsReferenceTableException('Unable to create table: '.$this->tableName);
            }
        }else{
            $this->log('Truncating existing table: '.$this->tableName);
            Waboot()->DB()->getQueryBuilder()::table($this->tableName)->truncate();
        }
    }

    /**
     * @param array $rowData
     * @return CSVRow
     */
    protected function createCSVColumnInstance(array $rowData): CSVRow
    {
        $rowData = array_combine(array_map('strtolower',array_keys($rowData)),array_values($rowData));
        return new CSVRow($rowData);
    }

    /**
     * @throws DBException
     */
    protected function parseCSVRow(): void
    {
        $this->log('- Parsing row #'.$this->currentCSVRowIndex);
        Query::on($this->tableName)->insert($this->currentCSVRow->getRowData());
    }
}
