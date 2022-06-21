import { Context, ReactNode, useContext, useMemo, useState } from "react";
import { NoticeType } from "_API/Types/NoticeType";
import { Syncable } from "_API/Types/Syncable";
import Button from "_Components/Atoms/Button/Button";
import Notice, { NoticeProps } from "_Components/Atoms/Notice/Notice";
import Modal from "_Components/Molecules/Modal/Modal";
import Table from "_Components/Molecules/Table/Table";
import {
  DataContextType,
  PassleDataContext,
} from "_Contexts/PassleDataContext";
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
  TableHeadings: JSX.Element;
  RenderItem: (item: T) => ReactNode;
};

const DataTable = <T extends Syncable>(props: DataTableProps<T>) => {
  const { setLoading } = useContext(PassleDataContext);
  const { data, pollingQueue, refreshItems, setCurrentPage } = useContext(
    props.context,
  );
  const [notice, setNotice] = useState<NoticeProps>(null);

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

  const setLoadingStatus = (isLoading: boolean) => {
    setWorking(isLoading);
    setLoading(isLoading);
  };

  const doWork = async (
    fn: () => Promise<void>,
    cb?: () => void,
    successMessage?: string,
    isQueuedAction?: boolean,
  ) => {
    try {
      setLoadingStatus(true);

      await fn();
      await refreshItems();

      setLoadingStatus(false);
      setSelectedItems([]);

      setShowDeleteAllModal(false);
      setShowDeleteMultipleModal(false);

      if (successMessage) {
        const noticeContent = isQueuedAction ? (
          <>
            {successMessage}{" "}
            <a
              target="_blank"
              rel="noopener noreferrer"
              href="/wp-admin/tools.php?page=action-scheduler&orderby=schedule&order=desc&status=pending">
              View pending tasks &#187;
            </a>
          </>
        ) : (
          successMessage
        );

        setNotice({ type: "success", content: noticeContent });
      }

      if (cb) cb();
    } catch (e) {
      setLoadingStatus(false);

      setNotice({
        type: "error",
        content: "Oops, something went wrong. Please try again.",
      });

      if (cb) cb();
    }
  };

  return (
    <div>
      {/* Delete all modal */}
      <Modal
        title={`Delete all ${props.itemPlural.toLowerCase()}`}
        text={`Are you sure you want to delete all synced ${props.itemPlural.toLowerCase()}? You cannot undo this action.`}
        buttons={
          <>
            <Button
              content={`Delete ${props.itemPlural}`}
              loadingContent={`Deleting ${props.itemPlural}...`}
              disabled={working}
              onClick={(cb) =>
                doWork(
                  deleteAllItems,
                  cb,
                  `Successfully queued all ${props.itemPlural.toLowerCase()} for deletion.`,
                  true,
                )
              }
            />
            <Button
              variant="secondary"
              content="Cancel"
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
        }? You cannot undo this action.`}
        buttons={
          <>
            <Button
              content={`Delete ${props.itemPlural}`}
              loadingContent={`Deleting ${props.itemPlural}...`}
              disabled={working}
              onClick={(cb) =>
                doWork(
                  deleteSelectedItems,
                  cb,
                  `Successfully queued ${selectedItems.length} ${
                    selectedItems.length === 1
                      ? props.itemSingular
                      : props.itemPlural
                  } for deletion.`,
                  true,
                )
              }
            />
            <Button
              variant="secondary"
              content="Cancel"
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
        buttons={
          <Button content="OK" onClick={() => setShowErrorModal(false)} />
        }
        open={showErrorModal}
        onCancel={() => setShowErrorModal(false)}
      />
      {notice && (
        <Notice
          type="success"
          content={notice.content}
          onDismiss={() => setNotice(null)}
        />
      )}
      {data.pending_sync_count ? (
        <Notice
          type="info"
          content={`Processing ${data.pending_sync_count} ${
            data.pending_sync_count === 1 ? "task" : "tasks"
          }, page will refresh automatically...`}
        />
      ) : null}
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
              content={`Fetch Passle ${props.itemPlural}`}
              loadingContent={`Fetching Passle ${props.itemPlural}...`}
              disabled={working}
              onClick={(cb) =>
                doWork(
                  refreshList,
                  cb,
                  `Successfully fetched all ${props.itemPlural} from the Passle API.`,
                  false,
                )
              }
            />
            {selectedItems.length ? (
              <Button
                variant="secondary"
                content={`Sync Selected ${props.itemPlural}`}
                loadingContent={`Syncing ${props.itemPlural}...`}
                disabled={working}
                onClick={(cb) =>
                  doWork(
                    syncSelectedItems,
                    cb,
                    `Successfully queued ${selectedItems.length} ${
                      selectedItems.length === 1
                        ? props.itemSingular
                        : props.itemPlural
                    } to be synced.`,
                    true,
                  )
                }
              />
            ) : (
              <Button
                variant="secondary"
                content={`Sync All ${props.itemPlural}`}
                loadingContent={`Syncing ${props.itemPlural}...`}
                disabled={working}
                onClick={(cb) =>
                  doWork(
                    syncAllItems,
                    cb,
                    `Successfully queued all ${props.itemPlural} to be synced.`,
                    true,
                  )
                }
              />
            )}
            <Button
              variant="secondary"
              content={
                <span
                  className="dashicons dashicons-update"
                  style={{
                    animation: pollingQueue ? "spin 1s linear infinite" : "",
                  }}
                />
              }
              loadingContent={
                <span
                  className="dashicons dashicons-update"
                  style={{ animation: "spin 1s linear infinite" }}
                />
              }
              hideSpinner={true}
              disabled={working || pollingQueue}
              onClick={(cb) => doWork(refreshList, cb)}
            />
          </>
        }
        ActionsRight={
          <>
            {selectedItems.length ? (
              <Button
                variant="secondary"
                content={`Delete Selected ${props.itemPlural}`}
                disabled={!allSelectedItemsAreSynced || working}
                onClick={() => setShowDeleteMultipleModal(true)}
              />
            ) : (
              <Button
                variant="secondary"
                content={`Delete All Synced ${props.itemPlural}`}
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
                checked={
                  selectedItems.length === data.data.length &&
                  data.data.length > 0
                }
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
              <td colSpan={props.TableHeadings.props.children.length + 1}>
                No {props.itemPlural.toLowerCase()} found.
              </td>
            </tr>
          )
        }
      />
    </div>
  );
};

export default DataTable;
