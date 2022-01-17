<?php

/**
 * Picker Routes
 */
return [
    [
        'pattern' => 'picker/pages',
        'method'  => 'GET',
        'action'  => function () {
            $data = [];
            $ids = json_decode(get('ids')) ?? [];

            foreach ($ids as $id) {
                if ($page = $this->page($id)) {
                    $data[$id] = $page->panel()->toArray();
                }
            }

            return $data;
        }
    ]
];
