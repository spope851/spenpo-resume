<?php
class ResumeAPI {
    private $repository = null;
    private static $instance = null;

    private function __construct() {
        // Empty private constructor
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getRepository() {
        if ($this->repository === null) {
            $this->repository = new ResumeRepository();
        }
        return $this->repository;
    }

    public static function registerRoutes() {
        $instance = self::getInstance();
        add_action('rest_api_init', function() use ($instance) {
            register_rest_route('spenpo/v1', '/resume', [
                'methods' => 'GET',
                'callback' => [$instance, 'getResumeResponse'],
                'permission_callback' => '__return_true',
            ]);
        });
    }

    public function getResumeResponse() {
        return new WP_REST_Response($this->fetchResume(), 200);
    }

    public function fetchResume() {
        // Get all sections and merge them into one array
        $repository = $this->getRepository();
        $all_sections = array_merge(
            $repository->getTextSections(),
            $repository->getListSections(),
            $repository->getNestedSections()
        );

        // Sort sections by display_order
        usort($all_sections, function($a, $b) {
            return $a->display_order - $b->display_order;
        });

        // Group the results by section type
        $sections_by_id = [];
        
        // Process all sections in one loop
        foreach ($all_sections as $row) {
            if (!isset($sections_by_id[$row->id])) {
                $section = new stdClass();
                $section->id = $row->id;
                $section->title = $row->title;
                $section->defaultExpanded = (bool)$row->default_expanded;
                $section->content = new stdClass();
                $section->content->type = $row->content_type;

                // Initialize content structure based on type
                switch ($row->content_type) {
                    case 'text':
                        $section->content->textContent = [];
                        break;
                    case 'list':
                        $section->content->items = [];
                        break;
                    case 'nested':
                        $section->content->nestedSections = [];
                        break;
                }
                
                $sections_by_id[$row->id] = $section;
            }

            // Process content based on type
            switch ($row->content_type) {
                case 'text':
                    if ($row->label) {
                        $content = new stdClass();
                        $content->id = $row->content_id;
                        $content->label = $row->label;
                        $content->text = $row->content_text;
                        $sections_by_id[$row->id]->content->textContent[] = $content;
                    }
                    break;

                case 'list':
                    if ($row->text) {
                        $item = new stdClass();
                        $item->id = $row->content_id;
                        $item->text = $row->text;
                        $item->year = $row->year;
                        if ($row->link) $item->link = $row->link;
                        if ($row->year_link) $item->yearLink = $row->year_link;
                        $sections_by_id[$row->id]->content->items[] = $item;
                    }
                    break;

                case 'nested':
                    if ($row->nested_id && !isset($sections_by_id[$row->id]->content->nestedSections[$row->nested_id])) {
                        $nested = new stdClass();
                        $nested->id = $row->nested_id;
                        $nested->title = $row->nested_title;
                        $nested->linkTitle = $row->link_title;
                        $nested->href = $row->href;
                        $nested->subTitle = $row->nested_sub_title;
                        $nested->details = [];
                        $sections_by_id[$row->id]->content->nestedSections[$row->nested_id] = $nested;
                    }
                    
                    if ($row->nested_id && ($row->text || $row->detail_title || $row->detail_sub_title)) {
                        $detail = new stdClass();
                        $detail->id = $row->detail_id;
                        if ($row->text) $detail->text = $row->text;
                        if ($row->detail_title) $detail->title = $row->detail_title;
                        if ($row->detail_sub_title) $detail->subTitle = $row->detail_sub_title;
                        if ($row->indent) $detail->indent = $row->indent;
                        $sections_by_id[$row->id]->content->nestedSections[$row->nested_id]->details[] = $detail;
                    }
                    break;
            }
        }

        // Convert nested sections associative arrays to indexed arrays
        foreach ($sections_by_id as $section) {
            if ($section->content->type === 'nested') {
                $section->content->nestedSections = array_values($section->content->nestedSections);
            }
        }

        return array_values($sections_by_id);
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