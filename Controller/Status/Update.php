<?php

        namespace Veratad\AgeVerification\Controller\Status;

        use Magento\Framework\App\Action\Context;
        use Magento\Framework\View\Result\PageFactory;
        use Magento\Framework\Controller\Result\JsonFactory;

        class Update extends \Magento\Framework\App\Action\Action
        {

             /**
             * @var Magento\Framework\View\Result\PageFactory
             */
            protected $resultPageFactory;
            protected $resultJsonFactory;
            private $scopeConfig;
            protected $_veratadHistory;
            protected $_veratadAccount;
            protected $_orderFactory;
            protected $date;
            protected $orderRepository;

            /**
             * @param Context     $context
             * @param PageFactory $resultPageFactory
             */
            public function __construct(
                Context $context,
                PageFactory $resultPageFactory,
                JsonFactory $resultJsonFactory,
                \Veratad\AgeVerification\Model\HistoryFactory $db,
                \Veratad\AgeVerification\Model\AccountFactory $account,
                \Magento\Sales\Model\OrderFactory $orderFactory,
                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                \Magento\Sales\Model\ResourceModel\Order $orderResourceModel,
                \Magento\Framework\Stdlib\DateTime\DateTime $date
                )
            {

                $this->resultPageFactory = $resultPageFactory;
                $this->resultJsonFactory = $resultJsonFactory;
                $this->scopeConfig = $scopeConfig;
                 $this->_veratadHistory = $db;
                 $this->_veratadAccount = $account;
                 $this->_orderFactory = $orderFactory;
                 $this->date = $date;
                 $this->orderRepository = $orderRepository;
                return parent::__construct($context);
            }


            public function execute()
            {

                //$customerId = $this->getRequest()->getParam('customer_id');
                $username = $this->getRequest()->getParam('username');
                $action = $this->getRequest()->getParam('status');
                $front = $this->getRequest()->getParam('front_id');
                $back = $this->getRequest()->getParam('back_id');
                $detail = "TRUE";
                $timestamp = $this->date->gmtDate();
                $orderid = $this->getRequest()->getParam('order_id');

                if($action === "PASS" || $action === "FAIL"){
                $this->_veratadHistory->create()->setData(
                  array("veratad_action" => $action,
                  "veratad_detail" => "MANUAL OVERRIDE",
                  "veratad_confirmation" => "NONE",
                  "veratad_timestamp" => $timestamp,
                  "veratad_override" => $detail,
                  "veratad_order_id" => $orderid,
                  "veratad_override_user" => $username,
                  "veratad_id_front" => $front,
                  "veratad_id_back" => $back
                ))->save();

                $this->_veratadAccount->create()->setData(
                  array("veratad_action" => $action,
                  "veratad_detail" => "MANUAL OVERRIDE",
                  "veratad_confirmation" => "NONE",
                  "veratad_timestamp" => $timestamp,
                  "veratad_override" => $detail,
                  "veratad_override_user" => $username
                ))->save();

                $order = $this->orderRepository->get($orderid);
                $order->setVeratadAction($action);
                $order->save();

              }

            }
        }
