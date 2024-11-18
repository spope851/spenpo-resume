<?php
/**
 * Handles the [spenpo_resume] shortcode functionality.
 * 
 * @package Spenpo\Resume
 * @since 1.0.0
 */
class ResumeShortcode {
    /** @var ResumeAPI Instance of the Resume API */
    private $api;

    /**
     * Constructor initializes the API connection and registers the shortcode.
     */
    public function __construct() {
        $this->api = ResumeAPI::getInstance();
        add_shortcode('spenpo_resume', [$this, 'render']);
    }

    /**
     * Renders the resume content as HTML.
     * 
     * @return string HTML output of the resume
     */
    public function render() {
        // Get data using the singleton instance
        $sections = $this->api->fetchResume();
        
        /**
         * Fires before the resume is rendered.
         *
         * @since 1.0.0
         * 
         * @param array $sections The resume sections data
         */
        do_action('spenpo_resume_before_render', $sections);
        
        $dom = new DOMDocument('1.0', 'utf-8');

        /**
         * Creates a new DOM element with specified attributes.
         * 
         * @param DOMDocument $dom       The DOM document instance
         * @param string      $tag       HTML tag name
         * @param string      $class     CSS class name
         * @param string|null $id        Optional element ID
         * @param string|null $text      Optional text content
         * @param array       $attributes Optional additional attributes
         * 
         * @return DOMElement The created element
         */
        function createElement($dom, $tag, $class, $id = null, $text = null, $attributes = []) {
            $element = $dom->createElement($tag);
            $element->setAttribute('class', $class);
            
            if ($id) {
                $element->setAttribute('id', $class."-$id");
            }

            foreach ($attributes as $key => $value) {
                $element->setAttribute($key, $value);
            }
            
            if ($text) {
                $element_text = $dom->createTextNode($text);
                $element->appendChild($element_text);
            }

            return $element;
        }
        
        // Create a root container for all sections
        $root = createElement($dom, 'div', 'spenpo-resume-container');
        $dom->appendChild($root);
        
        foreach ($sections as $section) {
            // Generate HTML using DOMDocument
            $section_div = createElement($dom, 'div', "spenpo-resume-section", $section->id);
    
            // title
            $section_title = createElement($dom, 'p', "spenpo-resume-section-title", $section->id, $section->title);
            $section_div->appendChild($section_title);
    
            // content
            $section_content = createElement($dom, 'div', "spenpo-resume-section-content", $section->id);
    
            // get content
            if ($section->content->type === 'text') {
                foreach ($section->content->textContent as $content) {
                    $section_content_item = createElement($dom, 'p', "spenpo-resume-section-content-item", $content->id);
                    $section_content_label = createElement($dom, 'strong', "spenpo-resume-section-content-label", $content->id, $content->label . ": ");
                    $section_content_text_container = createElement($dom, 'span', "spenpo-resume-section-content-text-container", $content->id, $content->text);
                    $section_content_item->appendChild($section_content_label);
                    $section_content_item->appendChild($section_content_text_container);
                    $section_content->appendChild($section_content_item);
                }

                $section_div->appendChild($section_content);
            }
    
            if ($section->content->type === 'list') {
                $section_content_list = createElement($dom, 'ul', "spenpo-resume-section-content-list", $section->id);
                foreach ($section->content->items as $item) {
                    $section_content_list_item = createElement($dom, 'li', "spenpo-resume-section-content-list-item", $item->id);
                    
                    // content 
                    $section_content_list_item_content;
                    if (isset($item->link)) {
                        $section_content_list_item_content = createElement($dom, 'a', "spenpo-resume-section-content-list-item-content", $item->id, $item->text, ['href' => $item->link]);
                    } else {
                        $section_content_list_item_content = createElement($dom, 'span', "spenpo-resume-section-content-list-item-content", $item->id, $item->text);
                    }

                    $section_content_list_item->appendChild($section_content_list_item_content);
                    
                    // year
                    $section_content_list_item_year;
                    if (isset($item->yearLink)) {
                        $section_content_list_item_year = createElement($dom, 'a', "spenpo-resume-section-content-list-item-year", $item->id, $item->year, ['href' => $item->yearLink]);
                    } else {
                        $section_content_list_item_year = createElement($dom, 'span', "spenpo-resume-section-content-list-item-year", $item->id, $item->year);
                    }

                    $section_content_list_item->appendChild($section_content_list_item_year);
                    $section_content_list->appendChild($section_content_list_item);
                }
                $section_div->appendChild($section_content_list);
            }
    
            if ($section->content->type === 'nested') {
                $section_content_nested = createElement($dom, 'div', "spenpo-resume-section-content-nested", $section->id);
    
                $current_nested_item = null;
                foreach ($section->content->nestedSections as $item) {
                    // Only create new nested item container if we're on a new nested section
                    if ($current_nested_item === null || $current_nested_item !== $item->id) {
                        // section container
                        $section_content_nested_item = createElement($dom, 'div', "spenpo-resume-section-content-nested-item", $item->id);

                        // title container
                        $section_content_nested_item_title_container = createElement($dom, 'div', "spenpo-resume-section-content-nested-item-title-container", $item->id);
    
                        // title
                        if ($item->title) {
                            $section_content_nested_title = createElement($dom, 'span', "spenpo-resume-section-content-nested-item-title", $item->id, $item->title . ": ");
                            $section_content_nested_item_title_container->appendChild($section_content_nested_title);
                        }
    
                        // link
                        if ($item->linkTitle) {
                            $section_content_nested_link = createElement($dom, 'a', "spenpo-resume-section-content-nested-item-link", $item->id, $item->linkTitle, ['href' => $item->href]);
                            $section_content_nested_item_title_container->appendChild($section_content_nested_link);
                        }
    
                        // sub title
                        if ($item->subTitle) {
                            $section_content_nested_sub_title = createElement($dom, 'span', "spenpo-resume-section-content-nested-item-sub-title", $item->id, $item->subTitle);
                            $section_content_nested_item_title_container->appendChild($section_content_nested_sub_title);
                        }

                        $section_content_nested_item->appendChild($section_content_nested_item_title_container);
    
                        $current_nested_item = $item->id;
                    }
    
                    // Create detail content (previously in the nested query)
                    foreach ($item->details as $detail) {
                        $section_content_nested_item_content;
                        $detail_classes = "spenpo-resume-section-content-nested-item-text" . (isset($detail->title) ? "-container" : "");

                        if (isset($detail->indent)) {
                            $detail_classes .= " ml-$detail->indent";
                        }

                        if (isset($detail->title)) {
                            $section_content_nested_item_content = createElement($dom, 'div', $detail_classes, $detail->id);
    
                            // title
                            $section_content_nested_item_text_title_content = createElement($dom, 'span', "spenpo-resume-section-content-nested-item-text-title", $detail->id, $detail->title);
                            $section_content_nested_item_content->appendChild($section_content_nested_item_text_title_content);
    
                            // sub title
                            $section_content_nested_item_text_sub_title_content = createElement($dom, 'span', "spenpo-resume-section-content-nested-item-text-sub-title", $detail->id, $detail->subTitle);
                            $section_content_nested_item_content->appendChild($section_content_nested_item_text_sub_title_content);
                        } else {
                            $section_content_nested_item_content = createElement($dom, 'p', $detail_classes, $detail->id, $detail->text);
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
        
        /**
         * Filters the final HTML output of the resume.
         * 
         * @since 1.0.0
         * 
         * @param string $html     The generated HTML
         * @param array  $sections The resume sections data
         * @return string The filtered HTML
         */
        $html = apply_filters('spenpo_resume_html_output', $dom->saveHTML(), $sections);
        
        /**
         * Fires after the resume is rendered.
         *
         * @since 1.0.0
         * 
         * @param string $html     The final HTML output
         * @param array  $sections The resume sections data
         */
        do_action('spenpo_resume_after_render', $html, $sections);
        
        return $html;
    }
}

// Initialize shortcode
new ResumeShortcode(); 