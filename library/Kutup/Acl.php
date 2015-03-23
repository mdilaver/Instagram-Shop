<?php
class Kutup_Acl extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();
        //var_dump($auth->getIdentity());
        $authModel=new Application_Model_Auth();
        if (!$auth->hasIdentity()){
            //If user doesn't exist it will get the Guest account from "users" table Id=1
            $authModel->authenticate(array('login'=>'Guest','password'=>'shocks'));
        }

        $request=$this->getRequest();
        $aclResource=new Application_Model_AclResource();
        //Check if the request is valid and controller an action exists. If not redirects to an error page.
        if( !$aclResource->resourceValid($request)){
            $request->setControllerName('error');
            $request->setActionName('error');
            return;
        }

        $controller = $request->getControllerName();
        $action = $request->getActionName();
        //Check if the requested resource exists in database. If not it will add it
        if( !$aclResource->resourceExists($controller, $action)){
            $aclResource->createResource($controller,$action);
        }
        //Get role_id
        $role_id=$auth->getIdentity()->role_id;
        $role=Application_Model_Role::getById($role_id);
        $role=$role[0]->role;
        // setup acl
        $acl = new Zend_Acl();
        // add the role
        $acl->addRole(new Zend_Acl_Role($role));
        if($role_id==3){//If role_id=3 "Admin" don't need to create the resources
            $acl->allow($role);
        }else{
            //Create all the existing resources
            $resources=$aclResource->getAllResources();
            // Add the existing resources to ACL
            foreach($resources as $resource){
                $acl->add(new Zend_Acl_Resource($resource->getController()));

            }
            //Create user AllowedResources
            $userAllowedResources=$aclResource->getCurrentRoleAllowedResources($role_id);

            // Add the user permissions to ACL
            foreach($userAllowedResources as $controllerName =>$allowedActions){
                $arrayAllowedActions=array();
                foreach($allowedActions as $allowedAction){
                    $arrayAllowedActions[]=$allowedAction;
                }
                $acl->allow($role, $controllerName,$arrayAllowedActions);
            }
        }
        //Save ACL so it can be used later to check current user permissions
        Zend_Registry::set('acl', $acl);
        //Check if user is allowed to acces the url and redirect if needed
        if(!$acl->isAllowed($role,$controller,$action)){
            $request->setControllerName('error');
            $request->setActionName('access-denied');
            return;
        }
    }
}