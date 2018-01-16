<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Blog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Controller\Adminhtml\Category\Save;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\CategoryInterfaceFactory;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\Http;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Test for \Aheadworks\Blog\Controller\Adminhtml\Category\Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    const CATEGORY_ID = 1;

    /**
     * @var array
     */
    private $formData = ['id' => self::CATEGORY_ID, 'name' => 'Category'];

    /**
     * @var Save
     */
    private $action;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryMock;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $this->categoryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));

        $this->categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->categoryRepositoryMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo(self::CATEGORY_ID))
            ->will($this->returnValue($this->categoryMock));
        $this->categoryRepositoryMock->expects($this->any())
            ->method('save')
            ->with($this->equalTo($this->categoryMock))
            ->will($this->returnValue($this->categoryMock));
        $categoryDataFactoryMock = $this->getMock(
            CategoryInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $categoryDataFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->categoryMock));

        $this->resultRedirectMock = $this->getMock(
            Redirect::class,
            ['setPath'],
            [],
            '',
            false
        );
        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->will($this->returnSelf());
        $resultRedirectFactoryMock = $this->getMock(
            RedirectFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultRedirectMock));

        $dataObjectHelperMock = $this->getMock(
            DataObjectHelper::class,
            ['populateWithArray'],
            [],
            '',
            false
        );

        $requestMock = $this->getMock(Http::class, ['getPostValue'], [], '', false);
        $requestMock->expects($this->any())
            ->method('getPostValue')
            ->will($this->returnValue($this->formData));
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $sessionMock = $this->getMock(Session::class, ['unsFormData', 'setFormData'], [], '', false);
        $this->dataPersistorMock = $this->getMockForAbstractClass(DataPersistorInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $requestMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $resultRedirectFactoryMock,
                'session' => $sessionMock
            ]
        );

        $this->action = $objectManager->getObject(
            Save::class,
            [
                'context' => $context,
                'categoryRepository' => $this->categoryRepositoryMock,
                'categoryDataFactory' => $categoryDataFactoryMock,
                'dataObjectHelper' => $dataObjectHelperMock,
                'dataPersistor' => $this->dataPersistorMock
            ]
        );
    }

    /**
     * Testing of redirect while saving
     */
    public function testExecuteRedirect()
    {
        $this->resultRedirectMock->expects($this->atLeastOnce())
            ->method('setPath')
            ->with($this->equalTo('*/*/'));
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing of redirect if error is occur
     */
    public function testExecuteRedirectError()
    {
        $this->categoryRepositoryMock->expects($this->any())
            ->method('save')
            ->willThrowException(
                new \Magento\Framework\Validator\Exception()
            );
        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with('aw_blog_category', $this->formData);
        $this->resultRedirectMock->expects($this->atLeastOnce())
            ->method('setPath')
            ->with($this->equalTo('*/*/edit'));
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing that category saved
     */
    public function testExecuteCategorySave()
    {
        $this->categoryRepositoryMock->expects($this->atLeastOnce())
            ->method('save')
            ->with($this->equalTo($this->categoryMock));
        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('aw_blog_category');
        $this->action->execute();
    }

    /**
     * Testing that success message is added if category is saved
     */
    public function testExecuteSuccessMessage()
    {
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage');
        $this->action->execute();
    }
}
