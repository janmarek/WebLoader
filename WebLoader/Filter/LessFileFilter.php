<?php
namespace WebLoader\Filter;

/**
 * Less CSS File filter
 * Becouse LessCSS PHP import file only once, we need to create instanse of every LESS file, to import same mixins into different files
 *
 * @author Radovan Kepak ( kepak@atlascon.cz )
 * @license MIT
 */
class LessFileFilter{
    public function __invoke( $code, \WebLoader\Compiler $laoder, $file ){
        if(pathinfo($file, PATHINFO_EXTENSION) !== 'less')
            return $code;

        $less = new \lessc;
        $less->importDir = pathinfo($file, PATHINFO_DIRNAME) . '/';
        return $less->parse($code);
    }
}