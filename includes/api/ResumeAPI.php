<?php
class ResumeAPI {
    private $repository;

    public function __construct() {
        $this->repository = new ResumeRepository();
        $this->registerRoutes();
    }

    public function registerRoutes() {
        add_action('rest_api_init', function() {
            register_rest_route('spenpo/v1', '/resume', [
                'methods' => 'GET',
                'callback' => [$this, 'fetchResume'],
                'permission_callback' => '__return_true',
            ]);

            register_rest_route('spenpo/v1', '/posts/(?P<id>\d+)/blocks', [
                'methods' => 'GET',
                'callback' => [$this, 'getBlocks'],
                'permission_callback' => '__return_true',
            ]);
        });
    }

    public function fetchResume() {
        // Get all sections with their related content in one query for each type
        $text_sections = $this->repository->getTextSections();
    
        $list_sections = $this->repository->getListSections();
    
        $nested_sections = $this->repository->getNestedSections();
    
        // Group the results by section type
        $sections_by_id = [];
        
        // Process text sections
        foreach ($text_sections as $row) {
            if (!isset($sections_by_id[$row->id])) {
                $sections_by_id[$row->id] = [
                    'title' => $row->title,
                    'defaultExpanded' => (bool)$row->default_expanded,
                    'content' => [
                        'type' => 'text',
                        'textContent' => []
                    ]
                ];
            }
            if ($row->label) {
                $sections_by_id[$row->id]['content']['textContent'][] = [
                    'label' => $row->label,
                    'text' => $row->content_text
                ];
            }
        }
    
        // Process list sections
        foreach ($list_sections as $row) {
            if (!isset($sections_by_id[$row->id])) {
                $sections_by_id[$row->id] = [
                    'title' => $row->title,
                    'defaultExpanded' => (bool)$row->default_expanded,
                    'content' => [
                        'type' => 'list',
                        'items' => []
                    ]
                ];
            }
            if ($row->text) {
                $list_item = [
                    'text' => $row->text,
                    'year' => $row->year
                ];
                if ($row->link) $list_item['link'] = $row->link;
                if ($row->year_link) $list_item['yearLink'] = $row->year_link;
                $sections_by_id[$row->id]['content']['items'][] = $list_item;
            }
        }
    
        // Process nested sections
        foreach ($nested_sections as $row) {
            if (!isset($sections_by_id[$row->id])) {
                $sections_by_id[$row->id] = [
                    'title' => $row->title,
                    'defaultExpanded' => (bool)$row->default_expanded,
                    'content' => [
                        'type' => 'nested',
                        'nestedSections' => []
                    ]
                ];
            }
            
            if ($row->nested_id && !isset($sections_by_id[$row->id]['content']['nestedSections'][$row->nested_id])) {
                $sections_by_id[$row->id]['content']['nestedSections'][$row->nested_id] = [
                    'title' => $row->nested_title,
                    'linkTitle' => $row->link_title,
                    'href' => $row->href,
                    'subTitle' => $row->nested_sub_title,
                    'details' => []
                ];
            }
            
            if ($row->nested_id && ($row->text || $row->detail_title || $row->detail_sub_title)) {
                $detail = [];
                if ($row->text) $detail['text'] = $row->text;
                if ($row->detail_title) $detail['title'] = $row->detail_title;
                if ($row->detail_sub_title) $detail['subTitle'] = $row->detail_sub_title;
                if ($row->indent) $detail['indent'] = $row->indent;
                $sections_by_id[$row->id]['content']['nestedSections'][$row->nested_id]['details'][] = $detail;
            }
        }
    
        // Convert nested sections associative arrays to indexed arrays
        foreach ($sections_by_id as &$section) {
            if ($section['content']['type'] === 'nested') {
                $section['content']['nestedSections'] = array_values($section['content']['nestedSections']);
            }
        }
    
        return new WP_REST_Response(array_values($sections_by_id), 200);
    }

    public function getBlocks($data) {
        $post_content = get_post_field('post_content', $data['id']);
        $blocks = parse_blocks($post_content);
    
        if ( empty( $blocks ) ) {
            return new WP_Error( 'no_blocks', 'Invalid post', array( 'status' => 404 ) );
        }
    
        return $blocks;
    }
}

// Initialize API
new ResumeAPI(); 