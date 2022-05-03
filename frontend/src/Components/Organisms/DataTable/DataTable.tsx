import { Context, ReactNode, useContext, useMemo, useState } from "react";
import { Syncable } from "_API/Types/Syncable";
import Button from "_Components/Atoms/Button/Button";
import Modal from "_Components/Molecules/Modal/Modal";
import Table from "_Components/Molecules/Table/Table";
import { DataContextType } from "_Contexts/PassleDataContext";
import {
  deleteAll,
  deleteMany,
  refreshAll,
  syncAll,
  syncMany,
} from "_Services/SyncService";

export type DataTableProps<T extends Syncable> = {
  itemSingular: string;
  itemPlural: string;
  context: Context<DataContextType<T>>;
  TableHeadings: ReactNode;
  RenderItem: (item: T) => ReactNode;
};

const DataTable = <T extends Syncable>(props: DataTableProps<T>) => {
  const { data, refreshItems, setCurrentPage } = useContext(props.context);

  const [working, setWorking] = useState(false);

  const [showDeleteAllModal, setShowDeleteAllModal] = useState(false);
  const [showDeleteMultipleModal, setShowDeleteMultipleModal] = useState(false);
  const [showErrorModal, setShowErrorModal] = useState(false);

  const [selectedItems, setSelectedItems] = useState<string[]>([]);

  const allSelectedItemsAreSynced = useMemo(
    () =>
      data.data
        .filter((item) => selectedItems.includes(item.shortcode))
        .every((item) => item.synced),
    [selectedItems, data],
  );

  const refreshList = async () => {
    await refreshAll(props.itemPlural);
  };

  const syncAllItems = async () => {
    await syncAll(props.itemPlural);
  };

  const syncSelectedItems = async () => {
    await syncMany(props.itemPlural, {
      shortcodes: selectedItems,
    });
  };

  const deleteAllItems = async () => {
    await deleteAll(props.itemPlural);
  };

  const deleteSelectedItems = async () => {
    await deleteMany(props.itemPlural, {
      shortcodes: selectedItems,
    });
  };

  const doWork = async (fn: () => Promise<void>, cb: () => void) => {
    try {
      setWorking(true);

      await fn();
      await refreshItems();

      setWorking(false);
      setSelectedItems([]);

      setShowDeleteAllModal(false);
      setShowDeleteMultipleModal(false);

      cb();
    } catch (e) {
      setWorking(false);
      cb();

      setShowErrorModal(true);
    }
  };

  return (
    <div>
      {/* Delete all modal */}
      <Modal
        title={`Delete all ${props.itemPlural.toLowerCase()}`}
        text={`Are you sure you want to delete all synced ${props.itemPlural.toLowerCase()}? They will be deleted
        immediately. You cannot undo this action.`}
        buttons={
          <>
            <Button
              text={`Delete ${props.itemPlural}`}
              loadingText={`Deleting ${props.itemPlural}...`}
              disabled={working}
              onClick={(cb) => doWork(deleteAllItems, cb)}
            />
            <Button
              variant="secondary"
              text="Cancel"
              disabled={working}
              onClick={() => setShowDeleteAllModal(false)}
            />
          </>
        }
        open={showDeleteAllModal}
        onCancel={() => {
          if (!working) setShowDeleteAllModal(false);
        }}
      />
      {/* Delete multiple modal */}
      <Modal
        title={`Delete selected ${props.itemPlural}`}
        text={`Are you sure you want to delete ${
          selectedItems.length
        } selected ${
          selectedItems.length === 1 ? props.itemSingular : props.itemPlural
        }? ${
          selectedItems.length === 1 ? "It" : "They"
        } will be deleted immediately. You cannot undo this action.`}
        buttons={
          <>
            <Button
              text={`Delete ${props.itemPlural}`}
              loadingText={`Deleting ${props.itemPlural}...`}
              disabled={working}
              onClick={(cb) => doWork(deleteSelectedItems, cb)}
            />
            <Button
              variant="secondary"
              text="Cancel"
              disabled={working}
              onClick={() => setShowDeleteMultipleModal(false)}
            />
          </>
        }
        open={showDeleteMultipleModal}
        onCancel={() => {
          if (!working) setShowDeleteMultipleModal(false);
        }}
      />
      {/* Error modal */}
      <Modal
        title="Oops"
        text="Something went wrong, please try again."
        buttons={<Button text="OK" onClick={() => setShowErrorModal(false)} />}
        open={showErrorModal}
        onCancel={() => setShowErrorModal(false)}
      />
      <Table
        currentPage={data.current_page}
        itemsPerPage={data.items_per_page}
        totalItems={data.total_items}
        totalPages={data.total_pages}
        setCurrentPage={setCurrentPage}
        ActionsLeft={
          <>
            <Button
              variant="secondary"
              text={`Refresh ${props.itemPlural}`}
              loadingText={`Refreshing ${props.itemPlural}...`}
              disabled={working}
              onClick={(cb) => doWork(refreshList, cb)}
            />
            {selectedItems.length ? (
              <Button
                variant="secondary"
                text={`Sync Selected ${props.itemPlural}`}
                loadingText={`Syncing ${props.itemPlural}...`}
                disabled={working}
                onClick={(cb) => doWork(syncSelectedItems, cb)}
              />
            ) : (
              <Button
                variant="secondary"
                text={`Sync All ${props.itemPlural}`}
                loadingText={`Syncing ${props.itemPlural}...`}
                disabled={working}
                onClick={(cb) => doWork(syncAllItems, cb)}
              />
            )}
          </>
        }
        ActionsRight={
          <>
            {selectedItems.length ? (
              <Button
                variant="secondary"
                text={`Delete Selected ${props.itemPlural}`}
                disabled={!allSelectedItemsAreSynced || working}
                onClick={() => setShowDeleteMultipleModal(true)}
              />
            ) : (
              <Button
                variant="secondary"
                text={`Delete All Synced ${props.itemPlural}`}
                disabled={!data.data.length || working}
                onClick={() => setShowDeleteAllModal(true)}
              />
            )}
          </>
        }
        Head={
          <>
            <td id="cb" className="manage-column column-cb check-column">
              <input
                id="cb-select-all-1"
                type="checkbox"
                checked={selectedItems.length === data.data.length}
                onChange={(e) =>
                  setSelectedItems(
                    e.target.checked ? data.data.map((x) => x.shortcode) : [],
                  )
                }
              />
            </td>
            {props.TableHeadings}
          </>
        }
        Body={
          data.data.length ? (
            data.data.map((item) => (
              <tr key={item.shortcode}>
                <th scope="row" className="check-column">
                  <input
                    id="cb-select-1"
                    type="checkbox"
                    value={item.shortcode}
                    checked={selectedItems.includes(item.shortcode)}
                    onChange={(e) =>
                      setSelectedItems((state) =>
                        e.target.checked
                          ? [...state, item.shortcode]
                          : state.filter((x) => x !== item.shortcode),
                      )
                    }
                  />
                </th>
                {props.RenderItem(item)}
              </tr>
            ))
          ) : (
            <tr className="no-items">
              <td colSpan={4}>No {props.itemPlural.toLowerCase()} found.</td>
            </tr>
          )
        }
      />
    </div>
  );
};

export default DataTable;
