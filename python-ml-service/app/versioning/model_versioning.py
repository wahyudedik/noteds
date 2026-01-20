"""
Model versioning system for semantic versioning and version history
"""
import re
from typing import Dict, List, Optional, Tuple
from datetime import datetime
import logging

logger = logging.getLogger(__name__)


class ModelVersioning:
    """Handle model versioning with semantic versioning (major.minor.patch)"""
    
    def __init__(self):
        """Initialize versioning system"""
        pass
    
    def parse_version(self, version: str) -> Tuple[int, int, int]:
        """
        Parse version string to (major, minor, patch)
        
        Args:
            version: Version string (e.g., "1.2.3")
        
        Returns:
            Tuple of (major, minor, patch)
        """
        match = re.match(r'^(\d+)\.(\d+)\.(\d+)$', version)
        if not match:
            raise ValueError(f"Invalid version format: {version}. Expected format: major.minor.patch")
        
        return (int(match.group(1)), int(match.group(2)), int(match.group(3)))
    
    def format_version(self, major: int, minor: int, patch: int) -> str:
        """
        Format version tuple to string
        
        Args:
            major: Major version
            minor: Minor version
            patch: Patch version
        
        Returns:
            Version string
        """
        return f"{major}.{minor}.{patch}"
    
    def increment_version(
        self,
        current_version: str,
        version_type: str = 'patch'
    ) -> str:
        """
        Increment version based on type
        
        Args:
            current_version: Current version string
            version_type: Type of increment ('major', 'minor', 'patch')
        
        Returns:
            New version string
        """
        major, minor, patch = self.parse_version(current_version)
        
        if version_type == 'major':
            major += 1
            minor = 0
            patch = 0
        elif version_type == 'minor':
            minor += 1
            patch = 0
        elif version_type == 'patch':
            patch += 1
        else:
            raise ValueError(f"Invalid version_type: {version_type}. Must be 'major', 'minor', or 'patch'")
        
        return self.format_version(major, minor, patch)
    
    def get_next_version(
        self,
        existing_versions: List[str],
        version_type: str = 'patch'
    ) -> str:
        """
        Get next version based on existing versions
        
        Args:
            existing_versions: List of existing version strings
            version_type: Type of increment ('major', 'minor', 'patch')
        
        Returns:
            Next version string
        """
        if not existing_versions:
            return "1.0.0"
        
        # Sort versions and get latest
        try:
            sorted_versions = sorted(
                existing_versions,
                key=lambda v: self.parse_version(v),
                reverse=True
            )
            latest_version = sorted_versions[0]
            return self.increment_version(latest_version, version_type)
        except ValueError as e:
            logger.warning(f"Error parsing versions: {e}. Starting from 1.0.0")
            return "1.0.0"
    
    def determine_version_type(
        self,
        hyperparameters_changed: bool = False,
        architecture_changed: bool = False,
        retrain: bool = False
    ) -> str:
        """
        Determine version type based on changes
        
        Args:
            hyperparameters_changed: Whether hyperparameters changed
            architecture_changed: Whether model architecture changed
            retrain: Whether this is a retrain of existing model
        
        Returns:
            Version type ('major', 'minor', 'patch')
        """
        if architecture_changed:
            return 'major'  # Breaking change
        elif hyperparameters_changed:
            return 'minor'  # Significant change
        else:
            return 'patch'  # Minor update (retrain with same config)
    
    def create_version_entry(
        self,
        version: str,
        model_id: str,
        changes: Optional[Dict[str, Any]] = None
    ) -> Dict[str, Any]:
        """
        Create version history entry
        
        Args:
            version: Version string
            model_id: Model ID
            changes: Dictionary describing changes
        
        Returns:
            Version entry dictionary
        """
        return {
            'version': version,
            'model_id': model_id,
            'created_at': datetime.now().isoformat(),
            'changes': changes or {}
        }

