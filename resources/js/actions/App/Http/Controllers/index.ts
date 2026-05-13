import AuthController from './AuthController'
import ArticleController from './ArticleController'
import ActivityController from './ActivityController'
import TransparencyController from './TransparencyController'
import DonationController from './DonationController'
import DocumentController from './DocumentController'
import KeywordController from './KeywordController'
import DocumentCategoryController from './DocumentCategoryController'
import AdminController from './AdminController'
const Controllers = {
    AuthController: Object.assign(AuthController, AuthController),
ArticleController: Object.assign(ArticleController, ArticleController),
ActivityController: Object.assign(ActivityController, ActivityController),
TransparencyController: Object.assign(TransparencyController, TransparencyController),
DonationController: Object.assign(DonationController, DonationController),
DocumentController: Object.assign(DocumentController, DocumentController),
KeywordController: Object.assign(KeywordController, KeywordController),
DocumentCategoryController: Object.assign(DocumentCategoryController, DocumentCategoryController),
AdminController: Object.assign(AdminController, AdminController),
}

export default Controllers