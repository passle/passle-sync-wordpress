import { createContext, useState, ReactNode, useEffect } from "react";
import {
  fetchSyncedPosts,
  PasslePost,
  WordpressPost,
} from "__services/SyncService";

type PostDataContextType = {
  syncedPosts: WordpressPost[];
  unsyncedPosts: PasslePost[];
  dedupePosts: (unsyncedPosts: PasslePost[]) => PasslePost[];
  setSyncedPosts: (posts: WordpressPost[]) => void;
  setUnsyncedPosts: (posts: PasslePost[]) => void;
};

export const PostDataContext = createContext<PostDataContextType>({
  unsyncedPosts: [],
  syncedPosts: [],
  dedupePosts: () => [],
  setSyncedPosts: () => {},
  setUnsyncedPosts: () => {},
});

export type PostDataContextProviderProps = {
  children?: ReactNode;
};

export const PostDataContextProvider = (
  props: PostDataContextProviderProps
) => {
  const [syncedPosts, setSyncedPosts] = useState<WordpressPost[]>([]);
  const [unsyncedPosts, setUnsyncedPosts] = useState<PasslePost[]>([]);

  useEffect(() => {
    const initialFetch = async () => {
      let results = await fetchSyncedPosts(null);
      setSyncedPosts(results);
    };
    initialFetch();
  }, []);

  const dedupePosts = (unsyncedPosts: PasslePost[]) => {
    const syncedShortcodes = Array.isArray(syncedPosts)
      ? syncedPosts.map((p) => p.post_shortcode)
      : [];

    console.log(syncedPosts, unsyncedPosts);

    return Array.isArray(unsyncedPosts)
      ? unsyncedPosts.filter((p) => !syncedShortcodes.includes(p.PostShortcode))
      : [];
  };

  return (
    <PostDataContext.Provider
      value={{
        syncedPosts,
        setSyncedPosts,
        unsyncedPosts,
        setUnsyncedPosts,
        dedupePosts,
      }}
    >
      {props.children}
    </PostDataContext.Provider>
  );
};
