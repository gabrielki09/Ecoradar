export type ApiResponse<T> = {
    success: true;
    message: string;
    data: T;
};

export type ApiErrorResponse = {
    success: false
    message: string;
    code: string;
    errors?: Record<string, string[]>;
};
