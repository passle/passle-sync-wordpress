import { createContext, useState, ReactNode, useEffect } from "react";
import { Post } from "_API/Types/Post";
import { fetchPosts } from "_Services/SyncService";

type PostDataContextType = {
  posts: Post[];
  setPosts: (posts: Post[]) => void;
  refreshPostLists: () => Promise<void>;
};

export const PostDataContext = createContext<PostDataContextType>({
  posts: [],
  setPosts: () => {},
  refreshPostLists: async () => {},
});

export type PostDataContextProviderProps = {
  children?: ReactNode;
};

export const PostDataContextProvider = (
  props: PostDataContextProviderProps,
) => {
  const [posts, setPosts] = useState<Post[]>([]);

  const refreshPostLists = async () => {
    let postData = await fetchPosts(null);
    setPosts(postData.posts);
    return Promise.resolve();
  };

  useEffect(() => {
    (async () => {
      await refreshPostLists();
    })();
  }, []);

  return (
    <PostDataContext.Provider
      value={{
        posts,
        setPosts,
        refreshPostLists,
      }}>
      {props.children}
    </PostDataContext.Provider>
  );
};
