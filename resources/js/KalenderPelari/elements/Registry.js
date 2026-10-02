export class KpElementRegistry {
    constructor(defs) {
        this.defs = defs;
    }

    has(type) { return !!this.defs[type]; }

    listTypes() { return Object.keys(this.defs); }

    label(type) { return this.defs[type]?.label ?? type; }
    icon(type) { return this.defs[type]?.icon ?? 'fa-square'; }

    defaultElement(type, overrides = {}) {
        const d = this.defs[type];
        if (!d) throw new Error(`Unknown element type: ${type}`);
        return {
            id: `el_${Math.random().toString(36).slice(2, 10)}_${Date.now().toString(36)}`,
            element_type: type,
            x_mm: overrides.x_mm ?? 10,
            y_mm: overrides.y_mm ?? 10,
            width_mm: overrides.width_mm ?? d.defaultSize_mm.w,
            height_mm: overrides.height_mm ?? d.defaultSize_mm.h,
            rotation_deg: 0,
            z_index: 0,
            locked: false,
            visible: true,
            style_config: structuredClone({ ...d.defaultStyle }),
            content_json: structuredClone({ ...d.defaultContent }),
            data_binding: null,
            asset_id: d.hasAsset ? null : undefined,
            local_asset_ref: d.hasAsset ? null : undefined,
        };
    }

    isEditable(type) { return !!this.defs[type]?.editable; }
    hasAsset(type) { return !!this.defs[type]?.hasAsset; }
    bindsMonth(type) { return !!this.defs[type]?.bindsMonth; }
}
