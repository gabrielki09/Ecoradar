import type { ProfileType } from "./profile/ProfileTypes";
import type { UserResource } from "./user.types";

export type Onboarding = {
    required: boolean;
    next_step: 'collector' | 'company' | null;
};

export interface MeResponse {
    user: UserResource;
}

export interface LoginPayload {
    email: string;
    password: string;
};

export interface AuthResponse {
    user: UserResource;
    token: string;
    token_type: string,
    onboarding: Onboarding;
};

export type RegisterPayload = {
    name: string;
    email: string;
    address?: string;
    document: string;
    password: string;
    profile_type: ProfileType;
};
