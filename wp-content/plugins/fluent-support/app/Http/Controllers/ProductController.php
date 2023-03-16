<?php

namespace FluentSupport\App\Http\Controllers;

use FluentSupport\App\Http\Requests\ProductRequest;
use FluentSupport\App\Models\Product;
use FluentSupport\Framework\Request\Request;

class ProductController extends Controller
{
    /**
     * index method will return the list of product
     * @param Request $request
     * @param Product $product
     * @return array
     */
    public function index ( Request $request, Product $product )
    {
      return $product->getProducts( $request->getSafe('search') );
    }

    /**
     * get method will get product by id and return
     * @param Product $product
     * @param int $productId
     * @return array
     */
    public function get ( Product $product, $productId )
    {
        return $product->getProduct( $productId );
    }

    /**
     * creare method will create new product
     * @param Request $request
     * @param Product $product
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function create ( ProductRequest $request, Product $product )
    {
        $data = $request->all();

        return $product->createProduct( $data );
    }


    /**
     * update methd will update an exiting product by id
     * @param Request $request
     * @param Product $product
     * @param int $productId
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function update ( ProductRequest  $request, Product $product, $productId )
    {
        $data = $request->all();

        return $product->updateProduct( $productId, $data );
    }

    /**
     * delete method will delete an existing product by id
     * @param Product $product
     * @param int $productId
     * @return array
     */
    public function delete ( Product $product, $productId )
    {
        return $product->deleteProduct( $productId );
    }
}
