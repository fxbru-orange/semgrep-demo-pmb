<?php

// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DiffusionsController.php,v 1.15.2.21 2024/03/21 15:58:29 jparis Exp $

namespace Pmb\DSI\Controller;

use Pmb\Common\Helper\HelperEntities;
use Pmb\DSI\Helper\SubscriberHelper;
use Pmb\DSI\Models\Channel\RootChannel;
use Pmb\DSI\Models\Diffusion;
use Pmb\DSI\Models\DiffusionStatus;
use Pmb\DSI\Models\DSIParserDirectory;
use Pmb\DSI\Models\EventDiffusion;
use Pmb\DSI\Models\Product;
use Pmb\DSI\Orm\DiffusionOrm;

class DiffusionsController extends CommonController
{
    protected const VUE_NAME = "dsi/diffusions";

    /**
     *
     * {@inheritDoc}
     * @see \Pmb\DSI\Controller\CommonController::getBreadcrumb()
     */
    protected function getBreadcrumb()
    {
        global $msg;
        return "{$msg['dsi_menu']} {$msg['menu_separator']} {$msg['dsi_diffusions']}";
    }

    /**
     * Ajout diffusion
     */
    protected function addAction()
    {
        print $this->render($this->getFormData());
    }

    /**
     * Edition diffusion
     */
    protected function editAction()
    {
        global $id;
        $id = intval($id);

        print $this->render($this->getFormData($id));
    }

    /**
     * Liste des diffusions
     */
    protected function defaultAction()
    {
        global $show_private;
        $show_private = intval($show_private) ?? 0;
        $diffusion = new Diffusion();
        $diffusionStatus = new DiffusionStatus();

        $list = $show_private == 1 ? $diffusion->getList() : $diffusion->getFilteredList();
        foreach ($list as $diffusion) {
            $diffusion->fetchChannel();
            $diffusion->fetchSubscriberList();
            $diffusion->fetchLastDiffusion();
        }

        print $this->render([
            "list" => $list,
            "diffusionStatus" => $diffusionStatus->getList(),
			"channelsType" => $this->getChannelTypeList()
        ]);
    }

    /**
     * Recuperation donnees formulaire ajout/edition
     *
     * @param number $id
     * @return array[]
     */
    protected function getFormData($id = 0)
    {
        $diffusion = new Diffusion($id);

        $diffusion->fetchView();
        $diffusion->fetchItem();
        $diffusion->fetchChannel();
        $diffusion->fetchSubscriberList();
        $diffusion->fetchEvents();
        $diffusion->fetchLastDiffusion();
        $diffusion->fetchDiffusionDescriptors();

        $product = new Product();
        $diffusionStatus = new DiffusionStatus();

        return [
            "diffusion" => $diffusion,
            "channels" => $this->getChannelTypeList(),
            "diffusionStatus" => $diffusionStatus->getList(),
            "products" => $product->getList(),
            "empr" => $this->getEmprData()
        ];
    }

    public function save()
    {
        $this->data->id = intval($this->data->id);

        $diffusion = new Diffusion($this->data->id);
        $result = $diffusion->check($this->data);
        if ($result['error']) {
            $this->ajaxError($result['errorMessage']);
            exit();
        }

        $diffusion->setFromForm($this->data);
        if (0 == $this->data->id) {
            $diffusion->create();
        } else {
            $diffusion->update();
        }

        //Save des events de la diffusion
        foreach ($this->data->events as $eventDiffusion) {
            $eventDiffusionModel = new EventDiffusion($eventDiffusion->id, $diffusion->id);
            $result = $eventDiffusionModel->check($eventDiffusion);
            if (!$result) {
                $this->ajaxError($result['errorMessage']);
                exit();
            }

            if (isset($eventDiffusion->id) && 0 == $eventDiffusion->id) {
                $eventDiffusionModel->create();
            } else {
                $eventDiffusionModel->update();
            }
        }

        $this->ajaxJsonResponse($diffusion);
        exit();
    }

    public function delete()
    {
        $diffusion = new Diffusion($this->data->id);
        $result = $diffusion->delete();

        if ($result['error']) {
            $this->ajaxError($result['errorMessage']);
            exit();
        }
        $this->ajaxJsonResponse([
			'success' => true
        ]);
        exit();
    }

    public function getChannelTypeList()
    {
        $channelTypeList = [];
        $manifests = DSIParserDirectory::getInstance()->getManifests("Pmb/DSI/Models/Channel/");
        foreach ($manifests as $manifest) {
            $message = $manifest->namespace::getMessages();
            $channelTypeList[] = [
                "id" => RootChannel::IDS_TYPE[$manifest->namespace] ?? 0,
                "namespace" => $manifest->namespace,
                "name" => $message['name'],
                "manually" => $manifest->manually,
			    "compatibility" => $manifest->compatibility
            ];
        }

        return $channelTypeList;
    }

    public function getEntityList()
    {
        return $this->ajaxJsonResponse(HelperEntities::get_entities_labels());
    }

    /**
     * relie un tag a l'entite
     * @return array
     */
    public function unlinkTag()
    {
        $diffusion = new Diffusion();
        $delete = $diffusion->unlinkTag($this->data->numTag, $this->data->numEntity);
        return $this->ajaxJsonResponse($delete);
    }

    /**
     * Supprime le lien entre un tag et une entite
     * @return array
     */
    public function linkTag()
    {
        $diffusion = new Diffusion();
        $link = $diffusion->linkTag($this->data->numTag, $this->data->numEntity);
        return $this->ajaxJsonResponse($link);
    }

    public function renderView(int $idEntity)
    {
        $diffusion = new Diffusion($idEntity);
        $this->ajaxJsonResponse($diffusion->renderView());
    }

    public function previewView(int $idEntity, int $selectedAttachment = -1)
    {
        $diffusion = new Diffusion($idEntity);

        $type = 'text/html';
        if ($selectedAttachment == -1) {
            $preview = $diffusion->previewView();
        } else {
            $preview = $diffusion->previewAttachmentView($selectedAttachment);
            if (is_array($preview)) {
                $preview = $preview['contenu'];

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $type = $finfo->buffer($preview);
            }
        }

        $this->ajaxResponse($preview, $type);
    }

    public function duplicate()
    {
        if ($this->data->id != 0) {
            $diffusionToDuplicate = new Diffusion($this->data->id);
            $newDiffusion = $diffusionToDuplicate->duplicate();
            $this->ajaxJsonResponse($newDiffusion);
        }
    }

    public function deleteAll()
    {
        foreach($this->data->ids as $id) {
            $diffusion = new Diffusion($id);
            $result = $diffusion->delete();
            // if($result["error"]) {
            //     $this->ajaxError($result['errorMessage']);
            // }
        }
        $this->ajaxJsonResponse([ 'success' => true ]);
    }

    public function send($idDiffusion)
    {
        if (DiffusionOrm::exist($idDiffusion)) {
            $diffusion = new Diffusion($idDiffusion);
            if ($diffusion->automatic) {
                $this->ajaxJsonResponse($diffusion->send());
            }
        }
        $this->ajaxError("not sent");
    }
    /**
     * R�cup�re les donn�es n�cessaires au filtrage des DSI par emprunteur
     */
    public function getEmprData()
    {
        $result = array();

        $result["categ"] = SubscriberHelper::get_empr_categ();
        $result["groups"] = SubscriberHelper::get_empr_groups();

        return $result;
    }

    public function importModelTags()
	{
		$diffusion = new Diffusion($this->data->numEntity);
		$diffusion->importModelTags();
		$this->ajaxJsonResponse($diffusion->tags);
	}

    public function addPortalDiffusion()
    {
        if(intval($this->data->id) == 0) {
            $this->ajaxError("diffusion not found");
        }
        $diffusion = new Diffusion(intval($this->data->id));
        $portalDiffusion = $diffusion->addPortalDiffusion();
        if($portalDiffusion === false) {
            $this->ajaxError("portal diffusion not created");
            exit();
        }
        $this->ajaxJsonResponse($portalDiffusion);
    }
}
