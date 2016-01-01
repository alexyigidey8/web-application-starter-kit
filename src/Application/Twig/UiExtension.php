<?php

namespace Application\Twig;

/**
 * @author Borut Balažek <bobalazek124@gmail.com>
 */
class UiExtension extends \Twig_Extension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'application/ui';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'array_labels' => new \Twig_Function_Method(
                $this,
                'arrayLabels',
                array(
                    'is_safe' => array('html'),
                )
            ),
            'pagination' => new \Twig_Function_Method(
                $this,
                'pagination',
                array(
                    'is_safe' => array('html'),
                )
            ),
        );
    }

    /**
     * @return string
     */
    public function arrayLabels($array = array())
    {
        if (!count($array)) {
            return '';
        }

        $output = '<ul class="list-inline">';
        foreach ($array as $one) {
            $output .= '<li>'.$one.'</li>';
        }
        $output .= '</ul>';

        return $output;
    }

    /**
     * @return string
     */
    public function pagination($output)
    {
        return $output;
    }
}
