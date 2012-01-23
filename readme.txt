WebLoader
=========

Komponenta pro načítání CSS a JS souborů

Autor: Jan Marek
Licence: MIT

Použití
-------

Presenter:

	<?php

	use WebLoader\JavaScriptLoader;
	use WebLoader\VariablesFilter;
	use WebLoader\CssLoader;

	abstract class BasePresenter extends Presenter {

		protected function createComponentJs() {
			$js = new JavaScriptLoader;

			$js->tempUri = $this->getContext()->params['baseUri'] . "data/webtemp";
			$js->tempPath = WWW_DIR . "/data/webtemp";
			$js->sourcePath = WWW_DIR . "/js";

            if ($this->getContext()->params['productionMode'] === TRUE) {
			    $js->filters[] = new VariablesFilter(array(
				    // texyla
				    "baseUri" => $this->getContext()->params["baseUri"],
				    "texylaPreviewPath" => $this->link(":Texyla:preview"),
				    "texylaFilesPath" => $this->link(":Texyla:listFiles"),
				    "texylaFilesUploadPath" => $this->link(":Texyla:upload"),
			    ));
            } else {
                $js->joinFiles = FALSE;
            }

			return $js;
		}


		protected function createComponentCss() {
			$css = new CssLoader;

			$css->sourcePath = WWW_DIR . "/css";
			$css->tempUri = $this->getContext()->params['baseUri'] . "data/webtemp";
			$css->tempPath = WWW_DIR . "/data/webtemp";

            if ($this->getContext()->params['productionMode'] === TRUE) {
			    $css->filters[] = function ($code) {
				    return cssmin::minify($code, "remove-last-semicolon");
			    };
            } else {
			    $css->joinFiles = FALSE;
		    }

			return $css;
		}

	}

	?>

Šablona:

	{widget js 'jquery.js', 'texyla.js', 'web.js'}
	{widget css 'reset.css', 'page.css', 'libs/texyla.css', 'libs/jqueryui.css'}
