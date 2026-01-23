export type SharePlacementPosition =
  | 'sidebar'
  | 'grid_top'
  | 'below_title'
  | 'below_actions';

export const sharePlacement: {
  explorer: { enabled: boolean; position: SharePlacementPosition };
  groups: { enabled: boolean; position: SharePlacementPosition };
  posts: { enabled: boolean; position: SharePlacementPosition };
} = {
  explorer: { enabled: true, position: 'sidebar' },
  groups: { enabled: true, position: 'below_title' },
  posts: { enabled: true, position: 'below_actions' },
};
