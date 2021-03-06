<?php

namespace Tqdev\PhpCrudAdmin\Column;

use Tqdev\PhpCrudAdmin\Client\CrudApi;
use Tqdev\PhpCrudAdmin\Column\DefinitionService;
use Tqdev\PhpCrudAdmin\Document\TemplateDocument;

class ColumnService
{
    private $api;
    private $definition;

    public function __construct(CrudApi $api, DefinitionService $definition)
    {
        $this->api = $api;
        $this->definition = $definition;
    }

    public function hasTable(string $table, string $action): bool
    {
        return $this->definition->hasTable($table, $action);
    }

    private function getDataTypeValues(): array
    {
        $types = array('bigint', 'bit', 'blob', 'boolean', 'clob', 'date', 'decimal', 'double', 'float', 'geometry', 'integer', 'time', 'timestamp', 'varbinary', 'varchar');
        return array_combine($types, $types);
    }

    private function getTableNameValues(): array
    {
        $tables = $this->definition->getTableNames();
        return array_combine($tables, $tables);
    }

    private function getBooleanValues(): array
    {
        return array('no', 'yes');
    }

    private function makeForm(array $column): array
    {
        $form = array();
        foreach ($column as $key => $value) {
            $form[$key] = array('value' => $value, 'type' => 'text', 'required' => false);
            switch ($key) {
                case 'name':
                    $form[$key]['required'] = true;
                    break;
                case 'type':
                    $form[$key]['required'] = true;
                    $form[$key]['type'] = 'select';
                    $form[$key]['values'] = $this->getDataTypeValues();
                    break;
                case 'length':
                case 'precision':
                case 'scale':
                    $form[$key]['type'] = 'number';
                    break;
                case 'nullable':
                case 'pk':
                    $form[$key]['required'] = true;
                    $form[$key]['type'] = 'select';
                    $form[$key]['values'] = $this->getBooleanValues();
                    break;
                case 'fk':
                    $form[$key]['type'] = 'select';
                    $form[$key]['values'] = $this->getTableNameValues();
                    break;
            }
        }
        return $form;
    }

    public function createForm(string $table, string $action): TemplateDocument
    {
        $column = $this->definition->getNewColumn();

        $form = $this->makeForm($column);

        $variables = array(
            'table' => $table,
            'action' => $action,
            'form' => $form,
        );

        return new TemplateDocument('layouts/default', 'column/create', $variables);
    }

    public function create(string $table, string $action, /* object */ $column): TemplateDocument
    {
        $success = $this->api->createColumn($table, $column);

        $variables = array(
            'table' => $table,
            'action' => $action,
            'name' => $column['name'],
            'success' => $success,
        );

        return new TemplateDocument('layouts/default', 'column/created', $variables);
    }

    public function updateForm(string $table, string $action, string $name): TemplateDocument
    {
        $column = $this->definition->getColumn($table, $name);

        $form = $this->makeForm($column);

        $variables = array(
            'table' => $table,
            'action' => $action,
            'name' => $column['name'],
            'form' => $form,
        );

        return new TemplateDocument('layouts/default', 'column/update', $variables);
    }

    public function update(string $table, string $action, string $name, /* object */ $column): TemplateDocument
    {
        $success = $this->api->updateColumn($table, $name, $column);

        $variables = array(
            'table' => $table,
            'action' => $action,
            'name' => $column['name'] ?: $name,
            'success' => $success,
        );

        return new TemplateDocument('layouts/default', 'column/updated', $variables);
    }

    public function deleteForm(string $table, string $action, string $name): TemplateDocument
    {
        $column = $this->definition->getColumn($table, $name);

        $variables = array(
            'table' => $table,
            'action' => $action,
            'name' => $column['name'],
        );

        return new TemplateDocument('layouts/default', 'column/delete', $variables);
    }

    public function delete(string $table, string $action, string $name): TemplateDocument
    {
        $success = $this->api->deleteColumn($table, $name);

        $variables = array(
            'table' => $table,
            'action' => $action,
            'name' => $name,
            'success' => $success,
        );

        return new TemplateDocument('layouts/default', 'column/deleted', $variables);
    }

    public function _list(string $table, string $action): TemplateDocument
    {
        $data = $this->definition->getTable($table);

        $variables = array(
            'table' => $table,
            'action' => $action,
            'columns' => $data['columns'],
        );

        return new TemplateDocument('layouts/default', 'column/list', $variables);
    }
}
