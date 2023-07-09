<?php 
/**
 * @author Bernardo Fuentes
 * Date: 10/10/2022
 */
class ItivosBanner extends modules
{
	public $html = "";
    public function __construct()
    {
        $this->name ='itivos_banner';
        $this->displayName = "Itivos Banner";
        $this->description = $this->l('Agrega un banner al home de promociones o cualquier otra info a resaltar.');
        $this->category  ='front_office_features';
        $this->version = '1.0.1';
        $this->author = 'Bernardo Fuentes';
        $this->versions_compliancy = array('min'=>'1.0.0', 'max'=> __SYSTEM_VERSION__);
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');
        $this->template_dir = __DIR_MODULES__."itivos_banner/views/back/";
        $this->template_dir_front = __DIR_MODULES__."itivos_banner/views/front/";
        parent::__construct();

        $this->key_module = "de34103f763d1cafc0dd87bf3c8ab91d";
        $this->crontLink = __URI__.__ADMIN__."/module/".$this->name."/crontab?key=".$this->key_module."";
    }
    public function install()
    {
    	 if(!$this->registerHook("displayHead") ||
            !$this->registerHook("displayFrontHead") ||
            !$this->registerHook("displayFrontBottom") ||
            !$this->registerHook("displayBottom") ||
            !$this->registerHook("displayFrontHomeTop") ||
            !$this->defaultData() 
            ){
            return false;
        }
        return true;
    }
    public function uninstall()
    {
    	$return = true;
    	$return &= connect::execute("DELETE FROM ".__DB_PREFIX__. "configuration WHERE module = '".$this->name."'");
    	return $return;
    }
    public function defaultData()
    {
    	$langs = language::getLangs();
    	foreach ($langs as $key => $lang) {
	        Configuration::updateValue('background_'.$lang['id'], 
	                                   "1110x150",
	                                   'itivos_banner');
	        Configuration::updateValue('call_to_action_'.$lang['id'], 
	                                   "https://itivos.com",
	                                   'itivos_banner');
    	}
        return true;
    }
    public function getConfig()
    {
    	if (isIsset('submit_action')) {
    		unset($_POST['submit_action']);
    		unset($_POST['submit']);
    		foreach ($_POST as $key => $value) {
    			Configuration::updateValue($key, 
	                                       getValue($key),
	                                       'itivos_banner');
    		}
            $uri = array();
    		foreach ($_FILES as $key => $value) {
	    		if (isset($_FILES[$key])) {
	    			if (!empty($_FILES[$key]['name'])) {
	    				if ( !empty($_FILES[$key]["tmp_name"]) )  {
		                    $upload = uploadFile($_FILES[$key]);
		                    if ($upload['errors']==0) {
                                $uri_webp = imagenCreateWebp($upload['url']);
		                    	Configuration::updateValue($key, 
		                    							   $uri_webp,
	                                   					   'itivos_banner');
		                    }
		                }
	    			}
	    		}
    		}
            $_SESSION['message'] = "Banner actualizado correctamente";
            $_SESSION['type_message'] = "success";
            header("Location: ".__URI__.__ADMIN__."/modules/config/".$this->name."");
    	}
        $banners = array();
        foreach (language::getLangs() as $key => $lang) {
        	$data = configuration::getValue('background_'.$lang['id']);
        	if (str_contains($data, "1110x150")) {
	        	$banners['langs'][$lang['id']]['background'] = "modules/itivos_banner/views/front/". $data.".png";
        	}else {
	        	$banners['langs'][$lang['id']]['background'] = $data;
        	}
	        $banners['langs'][$lang['id']]['call_to_action'] = configuration::getValue('call_to_action_'.$lang['id']);
	        $banners['langs'][$lang['id']]['iso_code'] = $lang['iso_code'];
        }
    	$helper = new HelperForm();
        $helper->tpl_vars = array(
            'fields_values' => $banners,
            'languages' => language::getLangs(),
        );
        $helper->submit_action = "updateAction";
        $this->view->assign("banners", $banners);
        $this->html .= "<div class='main_app'>";
        $this->html .= $this->view->fetch($this->template_dir."view_banner.tpl");
        $this->html .= "</div>";
        echo $this->html;
        return $this->html .= $helper->renderForm(self::generateForm());
    }
    public function generateForm()
    {
        $form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Editar banner'),
                        'icon' => 'icon-cogs',
                    ),
                    'inputs' => array(
                        array(
                            'type' => 'file',
                            'label' => $this->l('BANNER'),
                            'name' => 'background',
                            'lang' => true,
                            'required' => false,
                            'desc' => $this->l("Archivo JPG, PNG Peso max:".$this->upload_max_size),
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('ENLACE'),
                            'name' => 'call_to_action',
                            'lang' => true,
                            'required' => true,
                            'desc' => $this->l("Al dar clic te llevará al enlace que especifiques en este campo."),
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Guardar configuración'),
                    ),
                ),
            );
        return $form;
    }
    public function hookDisplayFrontHomeTop($params = null)
    {
    	$banner = array();
        $banner['background'] = configuration::getValue('background_'.language::getIdLang($this->lang));
        $banner['call_to_action'] = configuration::getValue('call_to_action_'.language::getIdLang($this->lang));
        if (str_contains($banner['background'], "1110x150")) {
        	$banner['background'] = "modules/itivos_banner/views/front/".$banner['background'].".png";
        }
        $this->view->assign("banner", $banner);
        $this->view->display($this->template_dir_front."displayFrontHomeTop.tpl");
    }
}