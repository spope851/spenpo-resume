<?php
class ResumeShortcode {
    private $api;

    public function __construct() {
        require_once(plugin_dir_path(__FILE__) . '../api/ResumeAPI.php');
        $this->api = ResumeAPI::getInstance();
        add_shortcode('spenpo_resume', [$this, 'render']);
    }

    public function render() {
        // Get data using the singleton instance
        $sections = $this->api->fetchResume();
        
        $dom = new DOMDocument('1.0', 'utf-8');
        
        // Create a root container for all sections
        $root = $dom->createElement('div');
        $root->setAttribute('class', 'spenpo-resume-container');
        $dom->appendChild($root);
        
        foreach ($sections as $section) {
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
            if ($section->content->type === 'text') {
                foreach ($section->content->textContent as $content) {
                    $section_content_item = $dom->createElement('p');
                    $section_content_item->setAttribute('class', "spenpo-resume-section-content-item-$content->id");
                    $section_content_label = $dom->createElement('strong');
                    $section_content_label->setAttribute('class', "spenpo-resume-section-content-label-$content->id");
                    $section_content_label_text = $dom->createTextNode($content->label . ": ");
                    $section_content_label->appendChild($section_content_label_text);
                    $section_content_item->appendChild($section_content_label);
                    $section_content_text_container = $dom->createElement('span');
                    $section_content_text_container->setAttribute('class', "spenpo-resume-section-content-text-container-$content->id");
                    $section_content_text = $dom->createTextNode($content->text);
                    $section_content_text_container->appendChild($section_content_text);
                    $section_content_item->appendChild($section_content_text_container);
                    $section_content->appendChild($section_content_item);
                }
                $section_div->appendChild($section_content);
            }
    
            if ($section->content->type === 'list') {
                $section_content_list = $dom->createElement('ul');
                $section_content_list->setAttribute('class', "spenpo-resume-section-content-list-$section->id");
                foreach ($section->content->items as $item) {
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
                    if (isset($item->yearLink)) {
                        $section_content_list_item_year = $dom->createElement('a');
                        $section_content_list_item_year->setAttribute('href', $item->yearLink);
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
    
            if ($section->content->type === 'nested') {
                $section_content_nested = $dom->createElement('div');
                $section_content_nested->setAttribute('class', "spenpo-resume-section-content-nested-$section->id");
    
                $current_nested_item = null;
                foreach ($section->content->nestedSections as $item) {
                    // Only create new nested item container if we're on a new nested section
                    if ($current_nested_item === null || $current_nested_item !== $item->id) {
                        // container
                        $section_content_nested_item = $dom->createElement('div');
                        $section_content_nested_item->setAttribute('class', "spenpo-resume-section-content-nested-item-$item->id");
    
                        // title
                        if ($item->title) {
                            $section_content_nested_title = $dom->createElement('span');
                            $section_content_nested_title->setAttribute('class', "spenpo-resume-section-content-nested-item-title-$item->id");
                            $section_content_nested_title_text = $dom->createTextNode($item->title . ": ");
                            $section_content_nested_title->appendChild($section_content_nested_title_text);
                            $section_content_nested_item->appendChild($section_content_nested_title);
                        }
    
                        // link
                        if ($item->linkTitle) {
                            $section_content_nested_link = $dom->createElement('a');
                            $section_content_nested_link->setAttribute('class', "spenpo-resume-section-content-nested-item-link-$item->id");
                            $section_content_nested_link->setAttribute('href', $item->href);
                            $section_content_nested_link_text = $dom->createTextNode($item->linkTitle);
                            $section_content_nested_link->appendChild($section_content_nested_link_text);
                            $section_content_nested_item->appendChild($section_content_nested_link);
                        }
    
                        // sub title
                        if ($item->subTitle) {
                            $section_content_nested_sub_title = $dom->createElement('span');
                            $section_content_nested_sub_title->setAttribute('class', "spenpo-resume-section-content-nested-item-sub-title-$item->id");
                            $section_content_nested_sub_title_text = $dom->createTextNode($item->subTitle);
                            $section_content_nested_sub_title->appendChild($section_content_nested_sub_title_text);
                            $section_content_nested_item->appendChild($section_content_nested_sub_title);
                        }
    
                        $current_nested_item = $item->id;
                    }
    
                    // Create detail content (previously in the nested query)
                    foreach ($item->details as $detail) {
                        $section_content_nested_item_content;
                        if (isset($detail->title)) {
                            $section_content_nested_item_content = $dom->createElement('div');
                            $section_content_nested_item_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-$detail->id");
    
                            // title
                            $section_content_nested_item_text_title_content = $dom->createElement('span');
                            $section_content_nested_item_text_title_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-title-$detail->id");
                            $section_content_nested_item_text_title_content_text = $dom->createTextNode($detail->title);
                            $section_content_nested_item_text_title_content->appendChild($section_content_nested_item_text_title_content_text);
                            $section_content_nested_item_content->appendChild($section_content_nested_item_text_title_content);
    
                            // sub title
                            $section_content_nested_item_text_sub_title_content = $dom->createElement('span');
                            $section_content_nested_item_text_sub_title_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-sub-title-$detail->id");
                            $section_content_nested_item_text_sub_title_content_text = $dom->createTextNode($detail->subTitle);
                            $section_content_nested_item_text_sub_title_content->appendChild($section_content_nested_item_text_sub_title_content_text);
                            $section_content_nested_item_content->appendChild($section_content_nested_item_text_sub_title_content);
                        } else {
                            $section_content_nested_item_content = $dom->createElement('p');
                            $section_content_nested_item_content->setAttribute('class', "spenpo-resume-section-content-nested-item-text-$detail->id");
                            $section_content_nested_item_text_content_text = $dom->createTextNode($detail->text);
                            $section_content_nested_item_content->appendChild($section_content_nested_item_text_content_text);
                        }

                        if (isset($detail->indent)) {
                            $existing_classes = $section_content_nested_item_content->getAttribute('class');
                            $section_content_nested_item_content->setAttribute('class', "$existing_classes"." ml-$detail->indent");
                        }

                        $section_content_nested_item->appendChild($section_content_nested_item_content);
                    }
    
                    $section_content_nested->appendChild($section_content_nested_item);
                }
                $section_div->appendChild($section_content_nested);
            }
    
            // Append to root instead of directly to dom
            $root->appendChild($section_div);
        }
        
        // Convert DOMDocument to HTML string
        return $dom->saveHTML();
    }
}

// Initialize shortcode
new ResumeShortcode(); 