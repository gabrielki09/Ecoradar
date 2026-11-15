import type { ProfileType } from "./profile/ProfileTypes";

export interface UserResource {
    id: number;
    name: string;
    email: string;
    profile_type: ProfileType;
    onboarding_completed: boolean;
};
