<?php

namespace Passle\PassleSync\SyncHandlers;

interface ISyncHandler
{
    public function sync_all();
    public function sync_one(array $data);
    public function delete_all();
    public function delete_one(array $data);
}
