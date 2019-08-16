<?php

namespace Collections\Controller;


class Admin extends \Cockpit\AuthController
{


    public function index()
    {

        $collections = $this->module('collections')->getCollectionsInGroup(null, true);

        foreach ($collections as $collection => $meta) {
            $collections[$collection]['allowed'] = [
                'delete' => $this->module('cockpit')->hasaccess('collections', 'delete'),
                'create' => $this->module('cockpit')->hasaccess('collections', 'create'),
                'edit' => $this->module('collections')->hasaccess($collection, 'collection_edit'),
                'entries_create' => $this->module('collections')->hasaccess($collection, 'collection_create')
            ];
        }

        return $this->render('collections:views/index.php', compact('collections'));
    }

    public function _collections()
    {
        return $this->module('collections')->collections();
    }

    public function _find()
    {

        if ($this->param('collection') && $this->param('options')) {
            return $this->module('collections')->find($this->param('collection'), $this->param('options'));
        }

        return false;
    }

    public function collection($name = null)
    {

        if ($name && !$this->module('collections')->hasaccess($name, 'collection_edit')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$name && !$this->module('cockpit')->hasaccess('collections', 'create')) {
            return $this->helper('admin')->denyRequest();
        }

        $collection = [
            'name' => '',
            'label' => '',
            'color' => '',
            'fields' => [],
            'acl' => new \ArrayObject,
            'sortable' => false,
            'in_menu' => false
        ];

        if ($name) {

            $collection = $this->module('collections')->collection($name);

            if (!$collection) {
                return false;
            }
        }

        // get field templates
        $templates = [];

        foreach ($this->app->helper("fs")->ls('*.php', 'collections:fields-templates') as $file) {
            $templates[] = include($file->getRealPath());
        }

        foreach ($this->app->module("collections")->collections() as $col) {
            $templates[] = $col;
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper("acl")->getGroups() as $group => $superAdmin) {

            if (!$superAdmin) $aclgroups[] = $group;
        }

        // rules
        $rules = [
            'create' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.create.php"),
            'read'   => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.read.php"),
            'update' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.update.php"),
            'delete' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.delete.php"),
        ];

        return $this->render('collections:views/collection.php', compact('collection', 'templates', 'aclgroups', 'rules'));
    }

    public function save_collection()
    {

        $collection = $this->param('collection');
        $rules      = $this->param('rules', null);

        if (!$collection) {
            return false;
        }

        return $this->module('collections')->saveCollection($collection['name'], $collection, $rules);
    }

    public function entries($collection)
    {

        if (!$this->module('collections')->hasaccess($collection, 'entries_view')) {
            return $this->helper('admin')->denyRequest();
        }

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        $count = $this->module('collections')->count($collection['name']);

        $collection = array_merge([
            'sortable' => false,
            'color' => '',
            'icon' => '',
            'description' => ''
        ], $collection);

        $context = _check_collection_rule($collection, 'read', ['options' => ['filter' => []]]);

        if ($context && isset($context->options['fields'])) {
            foreach ($collection['fields'] as &$field) {
                if (isset($context->options['fields'][$field['name']]) && !$context->options['fields'][$field['name']]) {
                    $field['lst'] = false;
                }
            }
        }

        $view = 'collections:views/entries.php';

        if ($override = $this->app->path('#config:collections/' . $collection['name'] . '/views/entries.php')) {
            $view = $override;
        }

        return $this->render($view, compact('collection', 'count'));
    }

    public function entry($collection, $id = null)
    {

        if ($id && !$this->module('collections')->hasaccess($collection, 'entries_view')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$id && !$this->module('collections')->hasaccess($collection, 'entries_create')) {
            return $this->helper('admin')->denyRequest();
        }

        $collection    = $this->module('collections')->collection($collection);
        $entry         = new \ArrayObject([]);
        $excludeFields = [];

        if (!$collection) {
            return false;
        }

        $collection = array_merge([
            'sortable' => false,
            'color' => '',
            'icon' => '',
            'description' => ''
        ], $collection);

        if ($id) {

            //$entry = $this->module('collections')->findOne($collection['name'], ['_id' => $id]);
            $entry = $this->app->storage->findOne("collections/{$collection['_id']}", ['_id' => $id]);

            if (!$entry) {
                return false;
            }
        }

        $context = _check_collection_rule($collection, 'read', ['options' => ['filter' => []]]);

        if ($context && isset($context->options['fields'])) {
            foreach ($context->options['fields'] as $field => $include) {
                if (!$include) $excludeFields[] = $field;
            }
        }

        $view = 'collections:views/entry.php';

        if ($override = $this->app->path('#config:collections/' . $collection['name'] . '/views/entry.php')) {
            $view = $override;
        }

        return $this->render($view, compact('collection', 'entry', 'excludeFields'));
    }

    public function save_entry($collection)
    {

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        $entry = $this->param('entry', false);
        $isDuplicated = $this->param('duplicated', false);

        if (!$entry) {
            return false;
        }

        if (!isset($entry['_id']) && !$this->module('collections')->hasaccess($collection['name'], 'entries_create')) {
            return $this->helper('admin')->denyRequest();
        }

        if (isset($entry['_id']) && !$this->module('collections')->hasaccess($collection['name'], 'entries_edit')) {
            return $this->helper('admin')->denyRequest();
        }

        $entry['_mby'] = $this->module('cockpit')->getUser('_id');

        if (isset($entry['_id'])) {
            $_entry = $this->module('collections')->findOne($collection['name'], ['_id' => $entry['_id']]);
            $revision = !(json_encode($_entry) == json_encode($entry));
        } else {
            $entry['_by'] = $entry['_mby'];
            $revision = true;
            if ($isDuplicated) {
                $entries = $this->param('entries', []);
                foreach ($collection['fields'] as $field) {
                    if (isset($field['options']) && isset($field['options']['duplicate_auto_number']) && $field['options']['duplicate_auto_number']) {
                        $fieldValues = [];
                        $counter = 1;
                        $entryValue = $entry[$field['name']];
                        foreach ($entries as $en) {
                            $fieldValues[] = $en[$field['name']];
                        }
                        while (in_array($entryValue . '_copy(' . $counter . ')', $fieldValues)) {
                            $counter++;
                        }
                        $entry[$field['name']] = $entryValue . '_copy(' . $counter . ')';
                    }
                }
            }
        }

        $entry = $this->module('collections')->save($collection['name'], $entry, ['revision' => $revision]);
        foreach ($entry as $key => &$data) {
            if ($this->isJSON($data)) {
                $value = json_decode($data, true);
                if ($value) {
                    $data = $value;
                }
            }
        }
        unset($data);
        return $entry;
    }

    public function delete_entries($collection)
    {

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        if (!$this->module('collections')->hasaccess($collection['name'], 'entries_delete')) {
            return $this->helper('admin')->denyRequest();
        }

        $filter = $this->param('filter', false);

        if (!$filter) {
            return false;
        }

        $this->module('collections')->remove($collection['name'], $filter);

        return true;
    }

    public function update_order($collection)
    {

        $collection = $this->module('collections')->collection($collection);
        $entries = $this->param('entries');

        if (!$collection) return false;
        if (!$entries) return false;

        $_collectionId = $collection['_id'];

        if (is_array($entries) && count($entries)) {

            foreach ($entries as $entry) {
                $this->app->storage->save("collections/{$_collectionId}", $entry);
            }
        }

        return $entries;
    }

    private function convert_to_csv($input_array, $output_file_name, $delimiter)
    {
        /** open raw memory as file, no need for temp files, be careful not to run out of memory thought */
        $f = fopen('php://memory', 'w');
        /** loop through array  */
        foreach ($input_array as $line) {
            /** default php csv handler **/
            fputcsv($f, $line, $delimiter);
        }
        /** rewrind the "file" with the csv lines **/
        fseek($f, 0);
        /** modify header to be downloadable csv file **/
        header('Content-Encoding: UTF-8');
        header("Content-type: application/csv; charset=UTF-8");
        header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
        header("Pragma: no-cache");
        header("Expires: 0");
        echo "\xEF\xBB\xBF";

        /** Send file to browser for download */
        fpassthru($f);
    }

    public function export($collection)
    {

        if (!$this->app->module("cockpit")->hasaccess("collections", 'manage')) {
            return false;
        }

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) return false;

        $entries = $this->module('collections')->find($collection['name']);

        $exportData = [];
        $headCols = [];
        foreach ($collection['fields'] as $field) {
            $headCols[$field['name']] = isset($field['label']) && !empty($field['label']) ? $field['label'] : $field['name'];
        }
        $exportData[] = $headCols;

        //make data
        foreach ($entries as $item) {
            $rowData = [];
            //get data base on header column
            foreach ($headCols as $k => $v) {
                //format day column
                if (!isset($item[$k])) $item[$k] = '';
                if (in_array($k, ['modified', 'created'])) $item[$k] = date('j F, Y', $item[$k]);
                if ($k == "phone" || $k == "time")
                $rowData[] = '="' . $item[$k] . '"';
                else
                $rowData[] = $item[$k];
            }
            $exportData[] = $rowData;
        }

        $this->convert_to_csv($exportData, $collection['name'] . '.csv', ',');
        // return json_encode($entries, JSON_PRETTY_PRINT); 
        return '';
    }


    public function tree()
    {

        $collection = $this->app->param('collection');

        if (!$collection) return false;

        $items = $this->app->module('collections')->find($collection);

        if (count($items)) {

            $items = $this->helper('utils')->buildTree($items, [
                'parent_id_column_name' => '_pid',
                'children_key_name' => 'children',
                'id_column_name' => '_id',
                'sort_column_name' => '_o'
            ]);
        }

        return $items;
    }

    public function find()
    {

        $collection = $this->app->param('collection');
        $options    = $this->app->param('options');

        if (!$collection) return false;

        $collection = $this->app->module('collections')->collection($collection);

        if (isset($options['filter']) && is_string($options['filter'])) {
            $options['filter'] = $this->_filter($options['filter'], $collection);
        }

        $entries = $this->app->module('collections')->find($collection['name'], $options);
        $count   = $this->app->module('collections')->count($collection['name'], isset($options['filter']) ? $options['filter'] : []);
        $pages   = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
        $page    = 1;

        if ($pages > 1 && isset($options['skip'])) {
            $page = ceil($options['skip'] / $options['limit']) + 1;
        }

        return compact('entries', 'count', 'pages', 'page');
    }


    public function revisions($collection, $id)
    {

        if (!$this->module('collections')->hasaccess($collection, 'entries_edit')) {
            return $this->helper('admin')->denyRequest();
        }

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        $entry = $this->module('collections')->findOne($collection['name'], ['_id' => $id]);

        if (!$entry) {
            return false;
        }

        $revisions = $this->app->helper('revisions')->getList($id);


        return $this->render('collections:views/revisions.php', compact('collection', 'entry', 'revisions'));
    }

    protected function _filter($filter, $collection)
    {

        if ($this->app->storage->type == 'mongolite') {
            return $this->_filterLight($filter, $collection);
        }

        if ($this->app->storage->type == 'mongodb') {
            return $this->_filterMongo($filter, $collection);
        }

        if ($this->app->storage->type == 'mysql') {
            return $this->_filterMysql($filter, $collection);
            // return $filter;
        }

        return null;
    }

    protected function _filterLight($filter, $collection)
    {

        $allowedtypes = ['text', 'longtext', 'boolean', 'select', 'html', 'wysiwyg', 'markdown', 'code'];
        $criterias    = [];
        $_filter      = null;

        foreach ($collection['fields'] as $field) {

            if ($field['type'] != 'boolean' && in_array($field['type'], $allowedtypes)) {
                $criteria = [];
                $criteria[$field['name']] = ['$regex' => $filter];
                $criterias[] = $criteria;
            }

            if ($field['type'] == 'collectionlink') {
                $criteria = [];
                $criteria[$field['name'] . '.display'] = ['$regex' => $filter];
                $criterias[] = $criteria;
            }

            if ($field['type'] == 'location') {
                $criteria = [];
                $criteria[$field['name'] . '.address'] = ['$regex' => $filter];
                $criterias[] = $criteria;
            }
        }

        if (count($criterias)) {
            $_filter = ['$or' => $criterias];
        }

        return $_filter;
    }

    protected function _filterMongo($filter, $collection)
    {

        $allowedtypes = ['text', 'longtext', 'boolean', 'select', 'html', 'wysiwyg', 'markdown', 'code'];
        $criterias    = [];
        $_filter      = null;

        foreach ($collection['fields'] as $field) {

            if ($field['type'] != 'boolean' && in_array($field['type'], $allowedtypes)) {
                $criteria = [];
                $criteria[$field['name']] = ['$regex' => $filter, '$options' => 'i'];
                $criterias[] = $criteria;
            }

            if ($field['type'] == 'collectionlink') {
                $criteria = [];
                $criteria[$field['name'] . '.display'] = ['$regex' => $filter, '$options' => 'i'];
                $criterias[] = $criteria;
            }

            if ($field['type'] == 'location') {
                $criteria = [];
                $criteria[$field['name'] . '.address'] = ['$regex' => $filter, '$options' => 'i'];
                $criterias[] = $criteria;
            }
        }

        if (count($criterias)) {
            $_filter = ['$or' => $criterias];
        }

        return $_filter;
    }

    protected function _filterMysql($filter, $collection)
    {

        $allowedtypes = ['text', 'longtext', 'boolean', 'select', 'html', 'wysiwyg', 'markdown', 'code'];
        $criterias    = [];
        $_filter      = null;

        foreach ($collection['fields'] as $field) {

            if ($field['type'] != 'boolean' && in_array($field['type'], $allowedtypes)) {
                $criteria = [];
                $criteria[$field['name'] . '[~]'] = $filter;
                $criterias[] = $criteria;
            }

            if ($field['type'] == 'collectionlink') {
                $criteria = [];
                $criteria[$field['name'] . '.display' . '[~]'] = $filter;
                $criterias[] = $criteria;
            }

            if ($field['type'] == 'location') {
                $criteria = [];
                $criteria[$field['name'] . '.address' . '[~]'] = $filter;
                $criterias[] = $criteria;
            }
        }

        if (count($criterias)) {
            $_filter = ['OR' => $criterias];
        }
        return $_filter;
    }
}
