<?php
/**
 * File containing the ContentServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Test case for operations in the ContentServiceAuthorization using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentService
 * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
 * @group integration
 */
class ContentServiceAuthorizationTest extends BaseContentServiceTest
{
    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentThrowsUnauthorizedException()
    {
        if ( $this->isVersion4() )
        {
            $this->markTestSkipped( "This test requires eZ Publish 5" );
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // This call will fail with a "UnauthorizedException"
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        $this->createContentDraftVersion1();
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testLoadContentInfoThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'object', 10 );
        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        // $contentId contains a content object ID not accessible for anonymous
        $contentService->loadContentInfo( $contentId );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfoByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfoByRemoteId
     */
    public function testLoadContentInfoByRemoteIdThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // RemoteId of the "Anonymous User" in an eZ Publish demo installation
        $anonymousRemoteId = 'faaeb9be3bd98ed09f606fc16d144eca';

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentInfoByRemoteId( $anonymousRemoteId );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     */
    public function testLoadVersionInfoThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadVersionInfo( $contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo($contentInfo, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoWithSecondParameter
     */
    public function testLoadVersionInfoThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadVersionInfo( $contentInfo, 1 );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoById
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadVersionInfoById( $anonymousUserId );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoByIdWithSecondParameter
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadVersionInfoById( $anonymousUserId, 1 );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfo
     */
    public function testLoadContentByContentInfoThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentByContentInfo( $contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfoWithSecondParameter
     */
    public function testLoadContentByContentInfoThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentByContentInfo( $contentInfo, array( 'eng-US' ) );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfoWithThirdParameter
     */
    public function testLoadContentByContentInfoThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentByContentInfo( $contentInfo, array( 'eng-US' ), 1 );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByVersionInfo
     */
    public function testLoadContentByVersionInfoThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        // Load the current VersionInfo
        $versionInfo = $contentService->loadVersionInfo( $contentInfo );

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentByVersionInfo( $versionInfo );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo($versionInfo, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByVersionInfoWithSecondParameter
     */
    public function testLoadContentByVersionInfoThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        // Load the current VersionInfo
        $versionInfo = $contentService->loadVersionInfo( $contentInfo );

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentByVersionInfo( $versionInfo, array( 'eng-US' ) );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testLoadContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContent( $anonymousUserId );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentWithSecondParameter
     */
    public function testLoadContentThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContent( $anonymousUserId, array( 'eng-US' ) );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentWithThirdParameter
     */
    public function testLoadContentThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContent( $anonymousUserId, array( 'eng-US' ), 1 );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByRemoteId
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Remote id of the "Anonymous" user in a eZ Publish demo installation
        $anonymousRemoteId = 'faaeb9be3bd98ed09f606fc16d144eca';

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentByRemoteId( $anonymousRemoteId );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByRemoteIdWithSecondParameter
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Remote id of the "Anonymous" user in a eZ Publish demo installation
        $anonymousRemoteId = 'faaeb9be3bd98ed09f606fc16d144eca';

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentByRemoteId( $anonymousRemoteId, array( 'eng-US' ) );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByRemoteIdWithThirdParameter
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Remote id of the "Anonymous" user in a eZ Publish demo installation
        $anonymousRemoteId = 'faaeb9be3bd98ed09f606fc16d144eca';

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser( $pseudoEditor );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentByRemoteId( $anonymousRemoteId, array( 'eng-US' ), 1 );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateContentMetadataThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Get ContentInfo instance.
        $contentInfo = $content->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // Creates a metadata update struct
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();

        $metadataUpdate->remoteId = 'aaaabbbbccccddddeeeeffff11112222';
        $metadataUpdate->mainLanguageCode = 'eng-US';
        $metadataUpdate->alwaysAvailable = false;
        $metadataUpdate->publishedDate = new \DateTime( '1984/01/01' );
        $metadataUpdate->modificationDate = new \DateTime( '1984/01/01' );

        // This call will fail with a "UnauthorizedException"
        $contentService->updateContentMetadata(
            $contentInfo,
            $metadataUpdate
        );
        /* END: Use Case */
    }

    /**
     * Test for the deleteContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteContent
     */
    public function testDeleteContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Get ContentInfo instance
        $contentInfo = $contentVersion2->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentService->deleteContent( $contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Get ContentInfo instance
        $contentInfo = $content->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentService->createContentDraft( $contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft($contentInfo, $versionInfo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraftWithSecondParameter
     */
    public function testCreateContentDraftThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Get ContentInfo and VersionInfo instances
        $contentInfo = $content->contentInfo;
        $versionInfo = $content->getVersionInfo();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentService->createContentDraft( $contentInfo, $versionInfo );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentDrafts
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentDrafts
     */
    public function testLoadContentDraftsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentDrafts();
        /* END: Use Case */
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts($user)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentDrafts
     */
    public function testLoadContentDraftsThrowsUnauthorizedExceptionWithFirstParameter()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId( 'user', 14 );
        /* BEGIN: Use Case */
        // $administratorUserId is  the ID of the "Administrator" user in a eZ
        // Publish demo installation.

        $contentService = $repository->getContentService();

        // Load the user service
        $userService = $repository->getUserService();

        // Load the "Administrator" user
        $administratorUser = $userService->loadUser( $administratorUserId );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadContentDrafts( $administratorUser );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draftVersion2 = $this->createContentDraftVersion2();

        // Get VersionInfo instance
        $versionInfo = $draftVersion2->getVersionInfo();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // Create an update struct and modify some fields
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdate->setField( 'title', 'An awesome²³ story about ezp.', 'eng-GB' );

        $contentUpdate->initialLanguageCode = 'eng-US';

        // This call will fail with a "UnauthorizedException"
        $contentService->updateContent( $versionInfo, $contentUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testPublishVersionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentService->publishVersion( $draft->getVersionInfo() );
        /* END: Use Case */
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteVersion
     */
    public function testDeleteVersionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException", because "content"
        // "versionremove" permission is missing.
        $contentService->deleteVersion( $draft->getVersionInfo() );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersions() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersions()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersions
     */
    public function testLoadVersionsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Get ContentInfo instance of version 2
        $contentInfo = $contentVersion2->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadVersions( $contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCopyContent
     */
    public function testCopyContentThrowsUnauthorizedException()
    {
        $parentLocationId = $this->generateId( 'location', 167 );

        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createMultipleLanguageContentVersion2();

        // Get ContentInfo instance of version 2
        $contentInfo = $contentVersion2->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // Configure new target location
        $targetLocationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        $targetLocationCreate->priority = 42;
        $targetLocationCreate->hidden = true;
        $targetLocationCreate->remoteId = '01234abcdef5678901234abcdef56789';
        $targetLocationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $targetLocationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // This call will fail with a "UnauthorizedException"
        $contentService->copyContent(
            $contentInfo,
            $targetLocationCreate
        );
        /* END: Use Case */
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCopyContentWithThirdParameter
     */
    public function testCopyContentThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $parentLocationId = $this->generateId( 'location', 167 );

        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // Configure new target location
        $targetLocationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        $targetLocationCreate->priority = 42;
        $targetLocationCreate->hidden = true;
        $targetLocationCreate->remoteId = '01234abcdef5678901234abcdef56789';
        $targetLocationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $targetLocationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // This call will fail with a "UnauthorizedException"
        $contentService->copyContent(
            $contentVersion2->contentInfo,
            $targetLocationCreate,
            $contentService->loadVersionInfo( $contentVersion2->contentInfo, 1 )
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadRelations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadRelations
     */
    public function testLoadRelationsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // Remote id of the "Support" page of a eZ Publish demo installation.
        $supportRemoteId = 'affc99e41128c1475fa4f23dafb7159b';

        $versionInfo = $contentService->loadVersionInfo(
            $contentService->loadContentInfoByRemoteId(
                $supportRemoteId
            )
        );

        // Set media editor as current user
        $repository->setCurrentUser( $user );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadRelations( $versionInfo );
        /* END: Use Case */
    }

    /**
     * Test for the loadReverseRelations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadReverseRelations()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadReverseRelations
     */
    public function testLoadReverseRelationsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // Remote id of the "Support" page of a eZ Publish demo installation.
        $supportRemoteId = 'affc99e41128c1475fa4f23dafb7159b';

        $contentInfo = $contentService->loadContentInfoByRemoteId(
            $supportRemoteId
        );

        // Set media editor as current user
        $repository->setCurrentUser( $user );

        // This call will fail with a "UnauthorizedException"
        $contentService->loadReverseRelations( $contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the addRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testAddRelationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote id of the "Support" page of a eZ Publish demo installation.
        $supportRemoteId = 'affc99e41128c1475fa4f23dafb7159b';

        $draft = $this->createContentDraftVersion1();

        // Get the draft's version info
        $versionInfo = $draft->getVersionInfo();

        // Load other content object
        $support = $contentService->loadContentInfoByRemoteId( $supportRemoteId );

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentService->addRelation(
            $versionInfo,
            $support
        );
        /* END: Use Case */
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteRelation
     */
    public function testDeleteRelationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote ids of the "Support" and the "Community" page of a eZ Publish
        // demo installation.
        $supportRemoteId = 'affc99e41128c1475fa4f23dafb7159b';
        $communityRemoteId = '378acc2bc7a52400701956047a2f7d45';

        $draft = $this->createContentDraftVersion1();

        // Get the draft's version info
        $versionInfo = $draft->getVersionInfo();

        $support = $contentService->loadContentInfoByRemoteId( $supportRemoteId );
        $community = $contentService->loadContentInfoByRemoteId( $communityRemoteId );

        // Establish some relations
        $contentService->addRelation( $draft->getVersionInfo(), $support );
        $contentService->addRelation( $draft->getVersionInfo(), $community );

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentService->deleteRelation( $versionInfo, $support );
        /* END: Use Case */
    }

    /**
     * Test for the findContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findContent($query, $fieldFilters, $filterOnUserPermissions)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testFindContent
     */
    public function testFindContentWithUserPermissionFilter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // Set new media editor as current user
        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();

        // Create a search query for content objects about "eZ Publish"
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'name', Criterion\Operator::LIKE, '*ez*' )
            )
        );

        // Search for matching content
        $searchResultWithoutPermissions = $contentService->findContent( $query, array(), false );

        // Search for matching content
        $searchResultWithPermissions = $contentService->findContent( $query, array() );
        /* END: Use Case */

        $this->assertGreaterThan(
            $searchResultWithPermissions->count,
            $searchResultWithoutPermissions->count
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle($query, $fieldFilters, $filterOnUserPermissions)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testFindContent
     */
    public function testFindSingleWithUserPermissionFilter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // Set new media editor as current user
        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();

        // Create a search query for content objects about "eZ Publish"
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'name', Criterion\Operator::LIKE, 'Administrator users' )
            )
        );

        // Search for matching content
        $content = $contentService->findSingle( $query, array(), false );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $content
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle($query, $fieldFilters, $filterOnUserPermissions)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testFindContent
     */
    public function testFindSingleThrowsNotFoundExceptionWithUserPermissionFilter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // Set new media editor as current user
        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();

        // Create a search query for content objects about "eZ Publish"
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'name', Criterion\Operator::LIKE, 'Administrator users' )
            )
        );

        // This call will fail with a "NotFoundException", because the current
        // user has no access to the "Admin Users" user group
        $contentService->findSingle( $query, array(), true );
        /* END: Use Case */
    }

    /**
     * Creates a pseudo editor with a limitation to objects in the "Media/Images"
     * subtree.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    private function createAnonymousWithEditorRole()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        $user = $userService->loadAnonymousUser();
        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        // Assign "Editor" role with limitation to "Media/Images"
        $roleService->assignRoleToUser(
            $role,
            $user,
            new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation(
                array(
                    'limitationValues' => array( '/1/43/51/' )
                )
            )
        );

        $pseudoEditor = $userService->loadUser( $user->id );
        /* END: Inline */

        return $pseudoEditor;
    }
}
