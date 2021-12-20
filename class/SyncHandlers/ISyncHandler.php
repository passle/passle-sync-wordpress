<?php

namespace Passle\PassleSync\SyncHandlers;

interface ISyncHandler
{
    public function sync_all();
    public function sync_one($data);
    public function delete_one($data);
}
