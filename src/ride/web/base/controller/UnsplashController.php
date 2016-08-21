<?php

namespace ride\web\base\controller;

use ride\library\config\Config;
use ride\library\http\client\CurlClient;
use ride\library\orm\OrmManager;
use ride\library\validation\exception\ValidationException;

class UnsplashController extends AbstractController {
    
    protected $config;
    protected $orm;
    
    public function indexAction(Config $config, OrmManager $orm, CurlClient $curlClient) {
        $translator = $this->getTranslator();
        $form = $this->createFormBuilder();
        
        $form->addRow('search', 'string', array(
            'label' => $translator->translate('label.unsplash.category'),
            'attributes' => array(
                'placeholder' => $translator->translate('label.unsplash.search')
            ),
            'filters' => array(
                'trim' => array()
            ),
            'validators' => array(
                'required' => array()
            )
        ));
        
        $form = $form->build();
        
        
        if ($form->isSubmitted()) {
            try {
                $form->validate();
                
                $data = $form->getData();
                $searchString = $data['search'];
                
                $this->generateImageAction($searchString, $config, $orm, $curlClient);
                
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            } catch (\Exception $exception) {
                $this->addError($exception->getCode());
                $this->setJsonView($exception->getMessage());
                
            }
        }
        
        $view = $this->setTemplateView('assets/unsplash', array(
            'form' => $form->getView(),
            'unsplash' > true
        ));
    }
    
    public function generateImageAction($search, Config $config, OrmManager $orm, CurlClient $curlClient) {
        $this->config = $config;
        $this->orm = $orm;
        $baseUrl = 'https://api.unsplash.com/photos/search';
        $baseUrl = $baseUrl . '?query=' . $search;
        
        $response = $curlClient->get($baseUrl . '&client_id=' . $this->config->get('unsplash.id') . '&per_page=' . $this->config->get('unsplash.amount'));
        
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('error.unsplash', $response->getStatusCode());
        }
        
        $photos = json_decode($response->getBody());
        
        $assetModel = $this->orm->getModel('Asset');
        $assetFolderModel = $this->orm->getModel('AssetFolder');
        
        $assetFolderEntry = $assetFolderModel->getBy(array('match' => array('name' => 'Unsplash')));
        if (!$assetFolderEntry) {
            $assetFolderEntry = $assetFolderModel->createEntry();
            $assetFolderEntry->setLocale($this->getLocale());
            $assetFolderEntry->setName('Unsplash');
            $assetFolderModel->save($assetFolderEntry);
        }
        
        foreach ($photos as $photo) {
            $asset = $assetModel->createEntry();
            $asset->setLocale($this->getLocale());
            $asset->setName($photo->id);
            $asset->setValue($photo->urls->regular);
            $asset->setFolder($assetFolderEntry);
            $assetModel->save($asset);
        }
        
        $this->addSuccess('unsplash.generation.complete');
        
        $this->response->setRedirect($this->getUrl('assets.overview'));
        
        return;
    }
    
}