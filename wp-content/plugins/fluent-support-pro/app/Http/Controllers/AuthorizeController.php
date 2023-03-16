<?php
namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\App;
use FluentSupport\Framework\Request\Request;
use FluentSupport\App\Http\Controllers\Controller;

class AuthorizeController extends Controller
{
    public function handleAuthorize(Request $request)
    {
        wp_redirect(admin_url('admin.php?page=fluent-support#/help_scout?code=' . $request->get('code')));
    }
}