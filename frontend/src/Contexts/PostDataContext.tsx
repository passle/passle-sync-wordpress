import { createContext, useState, ReactNode, useEffect } from "react";
import {
  fetchPosts,
  PasslePost,
  WordpressPost,
} from "_Services/SyncService";

type PostDataContextType = {
  syncedPosts: WordpressPost[];
  unsyncedPosts: PasslePost[];
  setSyncedPosts: (posts: WordpressPost[]) => void;
  setUnsyncedPosts: (posts: PasslePost[]) => void;
  refreshPostLists: () => Promise<void>;
};

export const PostDataContext = createContext<PostDataContextType>({
  unsyncedPosts: [],
  syncedPosts: [],
  setSyncedPosts: () => {},
  setUnsyncedPosts: () => {},
  refreshPostLists: async () => {}
});

export type PostDataContextProviderProps = {
  children?: ReactNode;
};

export const PostDataContextProvider = (
  props: PostDataContextProviderProps
) => {
  const [syncedPosts, setSyncedPosts] = useState<WordpressPost[]>([]);
  const [unsyncedPosts, setUnsyncedPosts] = useState<PasslePost[]>([]);

  const refreshPostLists = async () => {
    let postData = await fetchPosts(null);
    setSyncedPosts(postData.syncedPosts);
    setUnsyncedPosts(postData.unsyncedPosts);
    return Promise.resolve();
  }

  useEffect(() => {
    (async () => {
      await refreshPostLists();
    })();
  }, []);

  return (
    <PostDataContext.Provider
      value={{
        syncedPosts,
        setSyncedPosts,
        unsyncedPosts,
        setUnsyncedPosts,
        refreshPostLists
      }}
    >
      {props.children}
    </PostDataContext.Provider>
  );
};
