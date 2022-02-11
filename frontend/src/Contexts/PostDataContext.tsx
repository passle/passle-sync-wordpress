import { createContext, useState, ReactNode, useEffect, useMemo } from "react";
import { PaginatedResponse } from "_API/Types/PaginatedResponse";
import { Post } from "_API/Types/Post";
import { getAllPosts } from "_Services/SyncService";

type PostDataContextType = {
  postData?: PaginatedResponse<Post>;
  setCurrentPage: (page: number) => Promise<void>;
  setItemsPerPage: (count: number) => void;
  refreshPostLists: (page?: number, perPage?: number) => Promise<void>;
};

export const PostDataContext = createContext<PostDataContextType>({
  postData: null,
  setCurrentPage: async () => {},
  setItemsPerPage: () => {},
  refreshPostLists: async () => {},
});

export type PostDataContextProviderProps = {
  children?: ReactNode;
};

export const PostDataContextProvider = (
  props: PostDataContextProviderProps,
) => {
  const [postData, setPostData] = useState<PaginatedResponse<Post>>();

  const setCurrentPage = async (page: number) =>
    await refreshPostLists(page, postData.items_per_page);

  const setItemsPerPage = async (count: number) =>
    await refreshPostLists(postData.current_page, count);

  const refreshPostLists = async (
    currentPage: number = 1,
    itemsPerPage: number = 20,
  ) => {
    const response = await getAllPosts({
      currentPage,
      itemsPerPage,
    });

    setPostData(response);
  };

  useEffect(() => {
    (async () => {
      await refreshPostLists();
    })();
  }, []);

  return (
    <PostDataContext.Provider
      value={{
        postData: postData,
        setCurrentPage,
        setItemsPerPage,
        refreshPostLists,
      }}>
      {props.children}
    </PostDataContext.Provider>
  );
};
