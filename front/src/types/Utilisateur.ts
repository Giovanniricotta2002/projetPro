import type { Droit } from './Droit'
import type { Message } from './Message'
import type { Forum } from './Forum'
import type { Moderations } from './Moderations'
import type { Post } from './Post'
import type { Machine } from './Machine'


export type UserStatus =
  | 'active'
  | 'inactive'
  | 'suspended'
  | 'pending'
  | 'banned'
  | 'deleted'

export interface UserStatusMetaInfo {
  key: UserStatus;
  name: string;
  label: string;
  description: string;
  login: boolean;
  accessible: boolean;
  temporary: boolean;
  color: string;
}

export interface Utilisateur {
  id: number;
  username: string;
  roles: string[];
  password?: string | null;
  dateCreation: string | null;
  anonimus: boolean | null;
  lastVisit?: string | null;
  mail?: string | null;
  status: UserStatusMetaInfo;
  updatedAt?: string | null;
  deletedAt?: string | null;
  droits?: Droit[];
  message?: Message | null;
  forums?: Forum[];
  moderations?: Moderations | null;
  cible?: Moderations | null;
  createdAt?: string | null;
  creationMachine?: Machine[]; // Remplacer par Machine[] si défini
  posts?: Post[]; // Remplacer par Post[] si défini
}

