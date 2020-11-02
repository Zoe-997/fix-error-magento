<?php
/*
 * @category: Magepow
 * @copyright: Copyright (c) 2014 Magepow (http://www.magepow.com/)
 * @licence: http://www.magepow.com/license-agreement
 * @author: MichaelHa
 * @create date: 2019-11-29 17:19:50
 * @LastEditors: MichaelHa
 * @LastEditTime: 2019-12-04 11:04:26
 */

namespace Magepow\Categories\Block\Widget;

class Categories extends \Magepow\Categories\Block\Categories implements \Magento\Widget\Block\BlockInterface
{
    protected function _construct()
    {
        $this->_jnitWidget();
        parent::_construct();
    }

    protected function _jnitWidget()
    {
        $data = $this->getData();
        if($data['slide']){
            $breakpoints = $this->getResponsiveBreakpoints();
            $total = count($breakpoints);
            $responsive = '[';
            foreach ($breakpoints as $size => $screen) {
                $responsive .=  isset($data[$screen]) ? '{"breakpoint": '.$size.', "settings": {"slidesToShow": '. $data[$screen] .'}}' : '';
                if($total-- > 1) $responsive .= ', ';
            }
            $responsive .= ']';
            $data['responsive'] = $responsive;
            $data['slides-To-Show'] = $data['visible'];
            // $data['swipe-To-Slide'] = 'true';
            $data['vertical-Swiping'] = $data['vertical'];
        }
        $data['jnit_widget'] =1 ;
        if(is_array($data)) $this->addData($data);
    }

    public function getLayout() 
    {
        return 'grid';
    }

    public function getHeading() 
    {
        return $this->getData('title');
    }    

    public function isShowDescription() 
    {
        return $this->getData('description');
    }    

    public function getSortAttribute() 
    {
        return $this->getData('sort_attribute');
    } 

    public function getCategories()
    {

        $categoryIds = $this->getData('categories');
        if(!$categoryIds) return;
        $sortAttribute = $this->getSortAttribute();
        $model = $this->categoryFactory->create();      
        $categories = $model->getCollection()
        ->addAttributeToSelect(['name', 'url_key', 'url_path', 'image', 'description'])
        ->addAttributeToSort($sortAttribute)
        ->addIdFilter($categoryIds)
        ->addIsActiveFilter();

        return $categories;
    }

    public function getPrcents()
    {
        return array(1 => '100%', 2 => '50%', 3 => '33.333333333%', 4 => '25%', 5 => '20%', 6 => '16.666666666%', 7 => '14.285714285%', 8 => '12.5%', 9 => '11.111111111%');
    }

    public function getResponsiveBreakpoints()
    {
        return array(1921=>'visible', 1920=>'widescreen', 1480=>'desktop', 1200=>'laptop', 992=>'notebook', 768=>'tablet', 576=>'landscape', 480=>'portrait', 361=>'mobile', 1=>'mobile');
    }

    public function getSlideOptions()
    {
        return array('autoplay', 'arrows', 'autoplay-Speed', 'dots', 'infinite', 'padding', 'vertical', 'vertical-Swiping', 'responsive', 'rows', 'slides-To-Show');
    }

    public function getFrontendCfg()
    { 
        if($this->getSlide()) return $this->getSlideOptions();

        $this->addData(array('responsive' =>json_encode($this->getGridOptions())));
        return array('padding', 'responsive');

        // return $this->getGridStyle();

    }

    public function getGridOptions()
    {
        $options = array();
        $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
        foreach ($breakpoints as $size => $screen) {
            $options[]= array($size-1 => $this->getData($screen));
        }
        return $options;

    }

    function getGridStyle($selector=' .products-grid .product-item')
    {
        $styles = '';
        $listCfg = $this->getData();
        $padding = $listCfg['padding'];
        $prcents = $this->getPrcents();
        $breakpoints = $this->getResponsiveBreakpoints(); ksort($breakpoints);
        $total= count($breakpoints);
        $i = $tmp = 1;
        foreach ($breakpoints as $key => $value) {
            $tmpKey = ( $i == 1 || $i == $total ) ? $value : current($breakpoints);;
            if($i >1){
                $styles .= ' @media (min-width: '. $tmp .'px) and (max-width: ' . ($key-1) . 'px) {' .$selector. '{padding: 0 '.$padding.'px; width: '.$prcents[$listCfg[$value]] .'} ' .$selector. ':nth-child(' .$listCfg[$value]. 'n+1){clear: left;}}';
                next($breakpoints);
            }
            if( $i == $total) $styles .= ' @media (min-width: ' . $key . 'px) {' .$selector. '{padding: 0 '.$padding.'px; width: '.$prcents[$listCfg[$value]] .'} ' .$selector. ':nth-child(' .$listCfg[$value]. 'n+1){clear: left;}}';
            $tmp = $key;
            $i++;
        }
        return '<style type="text/css">' .$styles. '</style>';       
    }

}
