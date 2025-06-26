<?php
namespace ContactUs\CustomPage\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;

class Post extends Action
{
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $storeManager;
    protected $messageManager;

    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        
        // Verify reCAPTCHA
        if (!$this->verifyReCaptcha($post['g-recaptcha-response'])) {
            $this->messageManager->addErrorMessage(__('Please verify that you are not a robot.'));
            return $this->_redirect('*/*/');
        }

        try {
            // Send Email
            $this->inlineTranslation->suspend();
            
            $sender = [
                'name' => $post['name'],
                'email' => $post['email']
            ];

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('custom_contact_email_template')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars([
                    'name' => $post['name'],
                    'email' => $post['email'],
                    'telephone' => $post['telephone'],
                    'message' => $post['comment']
                ])
                ->setFrom($sender)
                ->addTo('contact@medizinhub.com')
                ->getTransport();

            $transport->sendMessage();
            
            $this->inlineTranslation->resume();
            $this->messageManager->addSuccessMessage(
                __('Thanks for contacting us. We\'ll respond to you very soon.')
            );
            
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
        }
        
        return $this->_redirect('*/*/');
    }

    private function verifyReCaptcha($reCaptchaResponse)
    {
        $secret = '6Le5cmcqAAAAAOngc6LdzZdlgtVt2lnPHfDZIzNu'; // Replace with your secret key
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $verifyUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'secret' => $secret,
                'response' => $reCaptchaResponse
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $responseData = json_decode($response, true);
        return isset($responseData['success']) && $responseData['success'] === true;
    }
}