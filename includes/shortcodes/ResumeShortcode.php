<?php
class ResumeShortcode {
    private $repository;

    public function __construct() {
        $this->repository = new ResumeRepository();
        add_shortcode('spenpo_resume', [$this, 'render']);
    }

    public function render() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'resume_sections';
        
        // Get all sections with their content in a single query based on content_type
        $text_sections = $this->repository->getTextSections();
        $list_sections = $this->repository->getListSections();
        $nested_sections = $this->repository->getNestedSections();
    
        // Group results by section_id for easier processing
        $grouped_text = array_reduce($text_sections, function($acc, $item) {
            $acc[$item->id][] = $item;
            return $acc;
        }, []);
    
        $grouped_list = array_reduce($list_sections, function($acc, $item) {
            $acc[$item->id][] = $item;
            return $acc;
        }, []);
    
        $grouped_nested = array_reduce($nested_sections, function($acc, $item) {
            $acc[$item->id][] = $item;
            return $acc;
        }, []);
    
        $results = $wpdb->get_results("SELECT * FROM $table_name");
        $dom = new DOMDocument('1.0', 'utf-8');
        
        foreach ($results as $section) {
            // Generate HTML using DOMDocument
            $section_div = $dom->createElement('div');
    
            // section
            $section_div->setAttribute('class', "spenpo-resume-section-$section->id");
    
            // title
            $section_title = $dom->createElement('p');
            $section_title->setAttribute('class', "spenpo-resume-section-title-$section->id");
            $section_title_text = $dom->createTextNode($section->title);
            $section_title->appendChild($section_title_text);
            $section_div->appendChild($section_title);
    
            // content
            $section_content = $dom->createElement('div');
            $section_content->setAttribute('class', "spenpo-resume-section-content-$section->id");
    
            // get content
            if ($section->content_type === 'text') {
                $section_query = $grouped_text[$section->id] ?? [];
                foreach ($section_query as $content) {
                    $section_content_item = $dom->createElement('p');
                    $section_content_item->setAttribute('class', "spenpo-resume-section-content-item-$content->id");
                    $section_content_label = $dom->createElement('strong');
                    $section_content_label->setAttribute('class', "spenpo-resume-section-content-label-$content->id");
                    $section_content_label_text = $dom->createTextNode($content->label . ": ");
                    $section_content_label->appendChild($section_content_label_text);
                    $section_content_item->appendChild($section_content_label);
                    $section_content_text_container = $dom->createElement('span');
                    $section_content_text_container->setAttribute('class', "spenpo-resume-section-content-text-container-$content->id");
                    $section_content_text = $dom->createTextNode($content->content_text);
                    $section_content_text_container->appendChild($section_content_text);
                    $section_content_item->appendChild($section_content_text_container);
                    $section_content->appendChild($section_content_item);
                }
            }
    
            if ($section->content_type === 'list') {
                $section_content_list_items = $grouped_list[$section->id] ?? [];
                $section_content_list = $dom->createElement('ul');
                $section_content_list->setAttribute('class', "spenpo-resume-section-content-list-$section->id");
                foreach ($section_content_list_items as $item) {
                    $section_content_list_item = $dom->createElement('li');
                    $section_content_list_item->setAttribute('class', "spenpo-resume-section-content-list-item-$item->id");
                    
                    // content 
                    $section_content_list_item_content;
                    if (isset($item->link)) {
                        $section_content_list_item_content = $dom->createElement('a');
                        $section_content_list_item_content->setAttribute('href', $item->link);
                    } else {
                        $section_content_list_item_content = $dom->createElement('span');
                        $section_content_list_item_content->setAttribute('class', "spenpo-resume-section-content-list-item-content-$item->id");
                    }
                    $section_content_list_item_text = $dom->createTextNode($item->text);
                    $section_content_list_item_content->appendChild($section_content_list_item_text);
                    $section_content_list_item->appendChild($section_content_list_item_content);
                    
                    // year
                    $section_content_list_item_year;
                    if (isset($item->year_link)) {
                        $section_content_list_item_year = $dom->createElement('a');
                        $section_content_list_item_year->setAttribute('href', $item->year_link);
                    } else {
                        $section_content_list_item_year = $dom->createElement('span');
                        $section_content_list_item_year->setAttribute('class', "spenpo-resume-section-content-list-item-year-$item->id");
                    }
                    $section_content_list_item_year_text = $dom->createTextNode($item->year);
                    $section_content_list_item_year->appendChild($section_content_list_item_year_text);
                    $section_content_list_item->appendChild($section_content_list_item_year);
                    
                    $section_content_list->appendChild($section_content_list_item);
                }
                $section_div->appendChild($section_content_list);
            }
    
            if ($section->content_type === 'nested') {
                $section_content_nested = $dom->createElement('div');
                $section_content_nested->setAttribute('class', "spenpo-resume-section-content-nested-$section->id");
                $section_content_nested_items = $grouped_nested[$section->id] ?? [];
    
                $current_nested_item = null;
                foreach ($section_content_nested_items as $item) {
                    // Only create new nested item container if we're on a new nested section
                    if ($current_nested_item === null || $current_nested_item !== $item->nested_id) {
                        // container
                        $section_content_nested_item = $dom->createElement('div');
                        $section_content_nested_item->setAttribute('class', "spenpo-resume-section-content-nested-item-$item->nested_id");
    
                        // title
                        if ($item->nested_title) {
                            $section_content_nested_title = $dom->createElement('span');
                            $section_content_nested_title->setAttribute('class', "spenpo-resume-section-content-nested-item-title-$item->nested_id");
                            $section_content_nested_title_text = $dom->createTextNode($item->nested_title . ": ");
                            $section_content_nested_title->appendChild($section_content_nested_title_text);
                            $section_content_nested_item->appendChild($section_content_nested_title);
                        }
    
                        // link
                        if ($item->link_title) {
                            $section_content_nested_link = $dom->createElement('a');
                            $section_content_nested_link->setAttribute('class', "spenpo-resume-section-content-nested-item-link-$item->nested_id");
                            $section_content_nested_link->setAttribute('href', $item->href);
                            $section_content_nested_link_text = $dom->createTextNode($item->link_title);
                            $section_content_nested_link->appendChild($section_content_nested_link_text);
                            $section_content_nested_item->appendChild($section_content_nested_link);
                        }
    
                        // sub title
                        if ($item->nested_sub_title) {
                            $section_content_nested_sub_title = $dom->createElement('span');
                            $section_content_nested_sub_title->setAttribute('class', "spenpo-resume-section-content-nested-item-sub-title-$item->nested_id");
                            $section_content_nested_sub_title_text = $dom->createTextNode($item->nested_sub_title);
                            $section_content_nested_sub_title->appendChild($section_content_nested_sub_title_text);
                            $section_content_nested_item->appendChild($section_content_nested_sub_title);
                        }
    
                        $current_nested_item = $item->nested_id;
                    }
    
                    // Create detail content (previously in the nested query)
                    if ($item->detail_id) {
                        $section_content_nested_item_content;
                        if (isset($item->detail_title)) {
                            $section_content_nested_item_content = $dom->createElement('div');
                            $section_content_nested_item_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-$item->detail_id");
    
                            // title
                            $section_content_nested_item_text_title_content = $dom->createElement('span');
                            $section_content_nested_item_text_title_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-title-$item->detail_id");
                            $section_content_nested_item_text_title_content_text = $dom->createTextNode($item->detail_title);
                            $section_content_nested_item_text_title_content->appendChild($section_content_nested_item_text_title_content_text);
                            $section_content_nested_item_content->appendChild($section_content_nested_item_text_title_content);
    
                            // sub title
                            $section_content_nested_item_text_sub_title_content = $dom->createElement('span');
                            $section_content_nested_item_text_sub_title_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-sub-title-$item->detail_id");
                            $section_content_nested_item_text_sub_title_content_text = $dom->createTextNode($item->detail_sub_title);
                            $section_content_nested_item_text_sub_title_content->appendChild($section_content_nested_item_text_sub_title_content_text);
                            $section_content_nested_item_content->appendChild($section_content_nested_item_text_sub_title_content);
                        } else {
                            $section_content_nested_item_content = $dom->createElement('p');
                            $section_content_nested_item_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-$item->detail_id");
                            $section_content_nested_item_text_content_text = $dom->createTextNode($item->text);
                            $section_content_nested_item_content->appendChild($section_content_nested_item_text_content_text);
                        }
                        if ($item->indent) {
                            $existing_classes = $section_content_nested_item_content->getAttribute('class');
                            $section_content_nested_item_content->setAttribute('class', "$existing_classes"." ml-$item->indent");
                        }
                        $section_content_nested_item->appendChild($section_content_nested_item_content);
                    }
    
                    $section_content_nested->appendChild($section_content_nested_item);
                }
                $section_div->appendChild($section_content_nested);
            }
    
            $section_div->appendChild($section_content);
            $dom->appendChild($section_div);
        }
        
        // Convert DOMDocument to HTML string
        return $dom->saveHTML();
    }
}

// Initialize shortcode
new ResumeShortcode(); 