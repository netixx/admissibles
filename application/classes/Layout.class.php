<?php

/**
 * Classes de gestion du Layout
 * @author François Espinet
 * @version 1.0
 *
 */
class Layout {

	/**
	 * Contient les donnée <meta> </meta> et autres données du header qui ne sont pas les css et les js
	 * @var array
	 */
	protected $_meta = array(
			'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');

	/**
	 * Contient les css à ajouter
	 * @var array
	 */
	protected $_css = array();

	/**
	 * Contient les js à ajouter
	 * @var unknown_type
	 */
	protected $_js = array();

	/**
	 * titre de la page
	 * @var string
	 */
	public $_title = 'École Polytechnique - Logement des admissibles';
	/**
	 * menus de la page
	 * Le menu principal est ajouté dans le contruct.
	 * On peut rajouter des menus divers. (exemple : menu admin)
	 * @var array
	 */
	protected $_menu = null;

	/**
	 * Contenu de la page
	 * @var array
	 */
	protected $_content = array();

	/**
	 * Contient les messages pouvant être affichés sur l'application
	 * @var array
	 */
	protected $_messages = null;

	/**
	 * Propriété true dans le cas ou la page est non-trouvée
	 * @var boolean
	 */
	public $page404 = false;

	/**
	 * variable qui permet d'afficher ou non le menu d'administration
	 * false si la personne n'est pas dans l'interface d'administration
	 * @var boolean
	 */
	public $is_admin = false;

	/**
	 * balise doctype
	 * @var string
	 */
	const doctype = '<!DOCTYPE html>';

	/**
	 * Constantes pour gérer l'incorporation de js et des css
	 */
	const Prepend = 1;
	const Append = 2;
	const Js = 1;
	const Css = 2;

	/**
	 * Libraries js à ajouter à l'application
	 * @var array
	 * @access protected
	 */
	protected $_libraries = array('jquery/jquery-1.9.1.js',
			'jquery/jquery-ui-1.10.1.custom.min.js',
			'jquery/jquery.visited.js');

	/**
	 * Templates (css des libraries) à ajouter à l'application
	 * @var array
	 * @access protected
	 */
	protected $_templates = array('jquery/jquery-ui-1.10.1.custom.css');

	/**
	 * Constructeur
	 * @access public
	 * @return void
	 */
	public function __construct() {
		//ajout des liens pour l'icone de l'application
		$this->_meta[] = '<link href="' . HTTP_IMAGES_PATH
				. '/favicon.ico" type="image/x-icon" rel="shortcut icon">';
		$this->_meta[] = '<link href="' . HTTP_IMAGES_PATH
				. '/favicon.png" type="image/png" rel="icon">';

		//ajout des css de base
		$this->appendCss('layout.css');
		$this->appendCss('forms.css');
		$this->appendCss('menu.css');
		$this->appendCss('text.css');
		$this->appendCss('images.css');
		$this->appendCss('messages.css');
		//ajout du menu
		$this->addMenu('main.html');
		//ajout des js de base
		$this->appendJs('menu.js');
	}

	/* ********************************************************************************************************************* *

	                            Méthodes d'ajout de contenu

	 * ********************************************************************************************************************* */
	/**
	 * Assigner le titre de l'application (balise <title>)
	 * @access public
	 * @param string $sTitre
	 * @return void
	 */
	public function setTitle($sTitre) {
		$this->_title = $sTitre;
	}

	/**
	 * Ajoute une page dans la partie contenu du layout
	 * @access public
	 * @param string $page un chemin de fichier valide vers un fichier php "page" à inclure
	 * @return void
	 */
	public function addPage($page) {
		//démarrage du tampon (permet d'inclure sans renvoyer directement à l'affichage)
		ob_start();
		include($page);
		//récupération et vidage du tampon (sans affichage)
		$this->_content[] = ob_get_clean();
	}

	/**
	 * Ajoute du contenu dans la partie contenu du layout
	 * @access public
	 * @param string $sContent : du contenu sous forme html
	 * @return void
	 */
	public function addContent($sContent) {
		$this->_content[] = $sContent;
	}

	/**
	 * Ajout du contenu à la page en le positionnant avant le contenu courant
	 * @access public
	 * @param string $sContent : du contenu sous forme html
	 * @return void
	 */
	public function prependContent($sContent) {
		array_unshift($this->_content, $sContent);
	}

	/**
	 * ajoute un menu à la page
	 * Le menu principal est ajouté par défaut
	 * @access public
	 * @param string $sUrl l'url depuis 'template/menu/'
	 * @return void
	 */
	public function addMenu($sUrl) {
		$this->_menu[] = MENUS_PATH . '/' . $sUrl;
	}

	/**
	 * ajoute le head à la page
	 * @access public
	 * @param string $sHead
	 * @return void
	 */
	public function addHead($sHead) {
		$this->_meta[] = $sHead;
	}

	/**
	 * Ajoute le javascript à la page avec une url extérieure
	 * @access public
	 * @param string $sUrl l'url du script à ajouter
	 * @return void
	 */
	public function addWebJs($sUrl) {
		$this->_js[] = '<script type="text/javascript" src="' . $sUrl
				. '"></script>';
	}

	/**
	 * Ajoute un css à la fin des css déjà présents
	 * @access public
	 * @param string $sRelUrl l'url relative à partir de public/css/  avec l'extension
	 * @return void
	 */
	public function appendCss($sRelUrl) {
		$this->_add($sRelUrl, self::Css);

	}

	/**
	 * Ajoute un css au début des css déjà présents
	 * @access public
	 * @param string $sRelUrl l'url relative à partir de public/css/ avec l'extension
	 * @return void
	 */
	public function prependCss($sRelUrl) {
		$this->_add($sRelUrl, self::Css, self::Prepend);

	}

	/**
	 * Ajoute un script (js) à la fin des js déjà présents
	 * @access public
	 * @param string $sRelUrl l'url relative à partir de public/js/ avec l'extension
	 * @return void
	 */
	public function appendJs($sRelUrl) {
		$this->_add($sRelUrl, self::Js);
	}

	/**
	 * Ajoute un script (js) au début des js déjà présents
	 * @access public
	 * @param string $sRelUrl l'url relative à partir de public/js/  avec l'extension
	 * @return void
	 */
	public function prependJs($sRelUrl) {
		$this->_add($sRelUrl, self::Js, self::Prepend);
	}

	/**
	 * Ajoute un element au début ou à la fin du tableau $type donné en entrée
	 * @access protected
	 * @param string $sElement l'élément à ajouter
	 * @param int $type le type d'élément à ajouter (css ou js)
	 * @param int $placement le placement (prepend ou append)
	 * @return void
	 */
	protected function _add($sElement, $type, $placement = self::Append) {
		$element = $this->__generateUrl($sElement, $type);
		switch ($type) {
		case self::Css:
			if ($placement == self::Append) {
				$this->_css[] = $element;
			} else {
				array_unshift($this->_css, $element);
			}
			break;
		case self::Js:
			if ($placement == self::Append) {
				$this->_js[] = $element;
			} else {
				array_unshift($this->_js, $element);
			}
			break;
		default:
			$this->_meta[] = $element;
			break;
		}
	}

	/**
	 * Génère une url en fontion du type donné
	 * @access protected
	 * @param string $sUrl url relative au path du type de l'élément à ajouter
	 * @param int $nType type de l'élément à générer (css ou js)
	 * @return string
	 */
	protected function __generateUrl($sUrl, $nType) {
		if ($nType == self::Js) {
			return '<script type="text/javascript" src="' . HTTP_JS_PATH . '/'
					. $sUrl . '"></script>';
		} else {
			return '<link type="text/css" href="' . HTTP_CSS_PATH . '/' . $sUrl
					. '" rel="stylesheet" media="all" />';
		}

	}

	/**
	 * Ajoute un message dans l'application
	 * @author francois.espinet
	 * @param string $sMessage
	 * @param string $sLevel
	 */
	public function addMessage($sMessage, $sLevel) {
	    $_SESSION['messages'][] = '<div class="message '.$sLevel.'">'.$sMessage .'</div>';
	}

	/* ********************************************************************************************************************* *

	                            Méthodes de rendu (affichage) des contenus

	 * ********************************************************************************************************************* */
	/**
	 * Méthodes de rendu du header
	 * @access public
	 * @return string
	 */
	public function renderHead()
	{
		$sHead = '<head>' . "\n";
		$sHead .= $this->renderMeta() . $this->renderCss() . $this->renderJs();
		return $sHead . '<title>' . $this->_title . '</title>' . "\n". '</head>';
	}

	/**
	 * Méthodes de rendu des méta
	 * @access public
	 * @return string
	 */
	public function renderMeta()
	{
		$sMetas = '';
		foreach ($this->_meta as $sMeta) {
			$sMetas .= $sMeta . "\n";
		}

		return $sMetas;
	}

	/**
	 * Méthodes de rendu du javascript
	 * @access public
	 * @return string
	 */
	public function renderJs()
	{
		$sJs = $this->renderLibraries();
		if (count($this->_js)) {
			foreach ($this->_js as $saJs) {
				$sJs .= $saJs . "\n";
			}
		}
		return $sJs;
	}

	/**
	 * Ajout des bibliothèques définies dans la constant libraries
	 * @access protected
	 * @return string
	 */
	protected function renderLibraries()
	{
		$libraries = '';
		if (count($this->_libraries)) {
			foreach ($this->_libraries as $library) {
				$libraries .= '<script type="text/javascript" src="'. HTTP_LIBRARY_PATH . '/' . $library . '"></script>'. "\n";
			}
		}
		return $libraries;
	}

	/**
	 * Rendu du CSS (et des librairies)
	 * @access public
	 * @return string
	 */
	public function renderCss()
	{
		$sCss = $this->renderCssTemplates();
		if (count($this->_css)) {
			foreach ($this->_css as $saCss) {
				$sCss .= $saCss . "\n";
			}
			return $sCss;
		}
	}

	/**
	 * Rendu du template CSS (libraries css)
	 * @access protected
	 * @return string
	 */
	protected function renderCssTemplates()
	{
		$templates = "";
		if (count($this->_templates)) {
			foreach ($this->_templates as $template) {
				$templates .= '<link type="text/css" href="'
						. HTTP_LIBRARY_PATH . '/' . $template
						. '" rel="stylesheet" media="all" />' . "\n";
			}
		}
		return $templates;
	}

	/**
	 * Méthodes de rendu des contenus (tableau $_content)
	 * @access public
	 * @return string
	 */
	public function renderContent()
	{
		$sContents = '<div id="page-wrapper">'. "\n"
		            . $this->renderMessages() . "\n"
		            . '<div class="contenu" id="contenu">' ."\n";
		if ($this->_content !== array() && count($this->_content)) {
			foreach ($this->_content as $sContent) {
				$sContents .= $sContent . "\n";
			}
		}
		return $sContents . '</div> </div>';
	}

	/**
	 * Rendu des menus de l'application
	 * @access public
	 * @return string les menus de l'application
	 */
	public function renderMenu() {
		$sMenu = '<div id="app-menus-wrapper">';
		if ($this->_menu != null) {
		    foreach ($this->_menu as $menu) {
		        ob_start();
		        include($menu);
		        $sMenu .= ob_get_clean();
		    }
		}
		return $sMenu . '</div>';
	}

	public function renderMessages() {
	    $sMessages = '<div class="messages">';
	    if (isset($_SESSION['messages']) && $_SESSION['messages'] !== null) {
	        $this->appendCss('messages.css');
	        foreach ($_SESSION['messages'] as $message) {
	            $sMessages .= $message;
	        }
	        //les messages ont été rendus, on les détruits de la session
	        unset($_SESSION['messages']);
	    }
	    return $sMessages . '</div>';
	}
	/**
	 * Rendu du bandeau
	 * @access public
	 * @return string
	 */
	public function renderBandeau() {
		return '<div id="bandeau">'.file_get_contents(TEMPLATE_PATH . '/header.html').'</div>';
	}

	/**
	 * Rendu du pied de page
	 * @access public
	 * @return string
	 */
	public function renderFooter() {
		return '<div id="footer">'.file_get_contents(TEMPLATE_PATH . '/footer.html').'</div>';
	}

	/**
	 * Méthode lançant le rendu
	 * @access public
	 * @return string
	 */
	public function render() {
	    if ($this->page404) {
	        $this->addMessage('La page que vous avez demandé n\'a pas été trouvée', MSG_LEVEL_ERROR);
	    }
		return self::doctype . "\n"
		        . '<html>' . "\n" . $this->renderHead() . "\n"
		                . '<body>'
		                        . $this->renderBandeau() . "\n"
// 		                        . $this->renderMessages() . "\n"
		                        . $this->renderMenu() . "\n"
		                        . $this->renderContent() ."\n"
		                        . $this->renderFooter() . "\n"
		                . '</body>' . "\n"
				. '</html>' . "\n";
	}

	/**
	 * Méthode de rendu automatique (en cas d'echo $layout )
	 * @access public
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

}
