<?php

namespace App\Observers;

use App\Models\ShoppingDetail;

class ShoppinDetailObserver
{
    /**
     * Handle the ShoppingDetail "created" event.
     *
     * @param  \App\Models\ShoppingDetail  $shoppingDetail
     * @return void
     */
    public function created(ShoppingDetail $shoppingDetail)
    {
        //
    }

    /**
     * Handle the ShoppingDetail "updated" event.
     *
     * @param  \App\Models\ShoppingDetail  $shoppingDetail
     * @return void
     */
    public function updated(ShoppingDetail $shoppingDetail)
    {
        //
    }

    /**
     * Handle the ShoppingDetail "deleted" event.
     *
     * @param  \App\Models\ShoppingDetail  $shoppingDetail
     * @return void
     */
    public function deleted(ShoppingDetail $shoppingDetail)
    {
        //
    }

    /**
     * Handle the ShoppingDetail "restored" event.
     *
     * @param  \App\Models\ShoppingDetail  $shoppingDetail
     * @return void
     */
    public function restored(ShoppingDetail $shoppingDetail)
    {
        //
    }

    /**
     * Handle the ShoppingDetail "force deleted" event.
     *
     * @param  \App\Models\ShoppingDetail  $shoppingDetail
     * @return void
     */
    public function forceDeleted(ShoppingDetail $shoppingDetail)
    {
        //
    }
}
